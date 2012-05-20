<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\html;

use \zeptech\anno\Annotations;
use \zpt\pct\AbstractGenerator;
use \zpt\pct\CodeTemplateParser;
use \Exception;
use \ReflectionClass;

/**
 * This class generates html providers from a given page definition.  It can
 * also be used at runtime to retrieve instances of generated html providers.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class HtmlProvider extends AbstractGenerator {

  private static $_cache = array();

  /**
   * Retrieve an HtmlProvider instance for the specified page definition.  The
   * HtmlProvider must already have been generated.
   *
   * @param string $pageDef
   */
  public static function get($pageDef) {
    if (!array_key_exists($pageDef, self::$_cache)) {
      $actor = str_replace('\\', '_', $pageDef);
      $fq = "zeptech\\dynamic\\html\\$actor";
      self::$_cache[$pageDef] = new $fq();
    }
    return self::$_cache[$pageDef];
  }

  /*
   * ===========================================================================
   * Generator
   * ===========================================================================
   */

  private $_tmpl;

  public function __construct($outputPath) {
    parent::__construct($outputPath . '/zeptech/dynamic/html');

    $parser = new CodeTemplateParser();
    $this->_tmpl = $parser->parse(
      file_get_contents(__DIR__ . '/htmlProvider.tmpl.php'));
  }

  protected function _generate($className) {
    global $asWebPath;

    $pageDef = new ReflectionClass($className);
    $page = new Annotations($pageDef);
    if (!isset($page['page'])) {
      throw new Exception("$className is not a page definition");
    }

    $values = array(
      'actor' => str_replace('\\', '_', $className),
      'jscripts' => array(),
      'sheets' => array(),
      'fonts' => array()
    );

    $title = null;
    if (isset($page['page']['title'])) {
      $title = $page['page']['title'];
    }
    
    if (isset($page['template'])) {
      $templateClass = $this->_getTemplateClass($page['template'], $className);

      $template = new Annotations(new ReflectionClass($templateClass));
      $values['template'] = $templateClass;

      if (isset($template['title'])) {
        if ($title !== null) {
          $title = $template['title'] . ' - ' . $title;
        } else {
          $title = $template['title'];
        }
      }

      $values['jscripts'] = array_merge(
        $values['jscripts'],
        $template->asArray('script'));

      $values['sheets'] = array_merge(
        $values['sheets'],
        $template->asArray('css'));

      $values['fonts'] = array_merge(
        $values['fonts'],
        $template->asArray('font'));
    }
    $values['title'] = $title;

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
    }

    $values['baseJsPath'] = $asWebPath('/js/base.js');
    $values['resetCssPath'] = $asWebPath('/css/reset.css');
    $values['cdtCssPath'] = $asWebPath('/css/cdt.css');


    $values['jqueryWorkingPath'] = $asWebPath('/js/jquery.working.js');
    $values['utilityJsPath'] = $asWebPath('/js/utility.js');
    $values['jqueryDomPath'] = $asWebPath('/js/jquery-dom.js');
    $values['cdtJsPath'] = $asWebPath('/js/conductor.js');

    $values['jscripts'] = array_merge(
      $values['jscripts'],
      $page->asArray('script'));
    $values['jscripts'] = $this->_resolveResources($values['jscripts'], '/js');

    $values['sheets'] = array_merge(
      $values['sheets'],
      $page->asArray('css'));
    $values['sheets'] = $this->_resolveResources($values['sheets'], '/css');

    $values['fonts'] = array_merge(
      $values['fonts'],
      $page->asArray('font'));

    if (count($values['fonts']) === 0) {
      unset($values['fonts']);
    } else {
      $values['fonts'] = implode('|', str_replace(' ', '+', $values['fonts']));
    }

    $values['hasContent'] = false;
    if ($pageDef->hasMethod('getContent')) {
      $values['hasContent'] = true;
      $values['contentProvider'] = $className;
    }

    return $this->_tmpl->forValues($values);
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

  private function _resolveResources($paths, $relBase) {
    global $asWebPath;

    $resolved = array();
    foreach ($paths as $path) {
      if (substr($path, 0, 1) === '/') {
        $resolved[] = $asWebPath($path);
      } else {
        $resolved[] = $asWebPath("$relBase/$path");
      }
    }
    return $resolved;
  }
}
