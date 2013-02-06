<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\html;

use \zeptech\anno\Annotations;
use \zpt\cdt\compile\resource\ResourceDiscoverer;
use \zpt\cdt\di\DependencyParser;
use \zpt\cdt\di\Injector;
use \zpt\cdt\Conductor;
use \zpt\pct\AbstractGenerator;
use \zpt\util\file\GlobFileLister;
use \DirectoryIterator;
use \Exception;
use \ReflectionClass;

/**
 * This class generates html providers from a given page definition.  It can
 * also be used at runtime to retrieve instances of generated html providers.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class HtmlProvider extends AbstractGenerator {

  public static $actorNamespace = 'zpt\dyn\html';

  /* The type of environment for which HtmlProviders will be generated. */
  private $_env;

  /* Filesystem path to htdocs. Used to resolve script groups. */
  private $_htdocs;

  public function __construct($outputPath, $env) {
    parent::__construct($outputPath);

    $this->_htdocs = "$outputPath/htdocs";
    $this->_env = $env;
  }

  protected function getTemplatePath() {
    return __DIR__ . '/htmlProvider.tmpl.php';
  }

  protected function getValues($className) {
    $pageDef = new ReflectionClass($className);
    $page = new Annotations($pageDef);
    if (!isset($page['page'])) {
      throw new NotAPageDefinitionException($className);
    }

    $values = array(
      'env' => $this->_env,
      'jslibs' => array(),
      'fonts' => array()
    );

    if (isset($page['template'])) {
      $templateClass = $this->_getTemplateClass($page['template'], $className);
      $templateDef = new ReflectionClass($templateClass);
      $template = new Annotations($templateDef);

      $values['template'] = $templateClass;
      $tmplDependencies = DependencyParser::parse(Injector::generateBeanId($templateClass), $templateDef);
      if (count($tmplDependencies) > 0) {
        $values['tmplDependencies'] = $tmplDependencies['props'];
      }
    } else {
      $template = new Annotations();
    }
    
    $values['title'] = $this->_parseTitle($page, $template);

    // If the page definition specifies an authorization level then ensure
    // that it is enforced
    if (isset($page['auth'])) {
      $values['auth'] = $page['auth'];
    }

    // Determine whether JsApp support is needed and the theme to use.  The
    // default theme for JsApp pages is 'zpt', otherwise it is the default
    // jquery-ui theme
    $values['jsappsupport'] = isset($page['jsappsupport']);
    if (isset($page['page']['theme'])) {
      $values['uitheme'] = $page['page']['theme'];
    } else if ($values['jsappsupport']) {
      $values['uitheme'] = 'zpt';
    }  else {
      $values['uitheme'] = 'base';
    }

    $values['jQueryPath'] = 'http://ajax.googleapis.com/ajax/libs/jquery/' .
      Conductor::JQUERY_VERSION;
    $values['webRoot'] = _P('/');
    $values['jsPath'] = _P('/js');
    $values['jslibPath'] = _P('/jslib');
    $values['cssPath'] = _P('/css');

    $jsResources = new ResourceDiscoverer("$this->_htdocs/js", 'js');
    $values['coreScripts'] = $jsResources->discover('cdt.core');
    $values['utilScripts'] = $jsResources->discover('cdt.util');
    $values['widgetScripts'] = $jsResources->discover('cdt.widget');

    $cssResources = new ResourceDiscoverer("$this->_htdocs/css", 'css');
    $values['coreCss'] = $cssResources->discover('cdt.core');
    $values['widgetCss'] = $cssResources->discover('cdt.widget');

    $values['jslibs'] = array_merge(
      $template->asArray('jslib'),
      $page->asArray('jslib')
    );

    $values['jscripts'] = array_map(function ($script) {
      if (substr($script, 0, 1) !== '/') {
        $script = "/js/$script";
      }
      return _P($script);
    }, $this->_parseScripts($page, $template));

    $values['sheets'] = array_map(function ($sheet) {
      if (substr($sheet, 0, 1) !== '/') {
        $sheet = "/css/$sheet";
      }
      return _P($sheet);
    }, $this->_parseStylesheets($page, $template));

    $values['fonts'] = array_merge(
      $template->asArray('font'),
      $page->asArray('font')
    );

    if (count($values['fonts']) === 0) {
      unset($values['fonts']);
    } else {
      $values['fonts'] = implode('|', str_replace(' ', '+', $values['fonts']));
    }

    $values['hasContent'] = false;
    if ($pageDef->hasMethod('getContent')) {
      $values['hasContent'] = true;
      $values['contentProvider'] = $className;

      $dependencies = DependencyParser::parse('htmlProvider', $pageDef);
      $values['dependencies'] = $dependencies['props'];
    }

    return $values;
  }

  /*
   * Determine if the given template name is absolute or relative and if
   * relative append the namespace of the page class.
   */
  private function _getTemplateClass($template, $pageClass) {
    if (strpos($template, '\\') !== false) {
      return $template;
    }

    return substr($pageClass, 0, strrpos($pageClass, '\\') + 1) . $template;
  }

  /*
   * Parse the required javascripts from the given sets of page and template
   * annotations.  Scripts declared without a '.js' extension are considered
   * to be script groups.
   */
  private function _parseScripts($page, $template) {
    $jsResources = new ResourceDiscoverer("$this->_htdocs/js", 'js');

    $declared = array_merge(
      $template->asArray('script'),
      $page->asArray('script')
    );

    $resolved = array();
    foreach ($declared as $script) {
      if (substr($script, -3) === '.js') {
        $resolved[] = $script;
      } else {
        $resolved = array_merge($resolved, $jsResources->discover($script));
      }
    }
    return $resolved;
  }

  /*
   * Parse the required stylesheets from the given sets of page and template
   * annotations.  Stylesheets declared without a '.css' extension are
   * considered to be script groups.
   */
  private function _parseStylesheets($page, $template) {
    $cssResources = new ResourceDiscoverer("$this->_htdocs/css", 'css');

    $declared = array_merge(
      $template->asArray('css'),
      $page->asArray('css')
    );

    $resolved = array();
    foreach ($declared as $stylesheet) {
      if (substr($stylesheet, -4) === '.css') {
        $resolved[] = $stylesheet;
      } else {
        $resolved = array_merge(
          $resolved,
          $cssResources->discover($stylesheet)
        );
      }
    }
    return $resolved;
  }

  /*
   * Parse the title from the given sets of page and template annotations.
   */
  private function _parseTitle($page, $template) {
    $title = null;
    if (isset($page['page']['title'])) {
      $title = $page['page']['title'];
    }

    if (isset($template['title'])) {
      if ($title !== null) {
        $title = $template['title'] . ' - ' . $title;
      } else {
        $title = $template['title'];
      }
    }
    return $title;
  }

}
