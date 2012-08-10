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
use \zpt\cdt\di\DependencyParser;
use \zpt\pct\AbstractGenerator;
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

  protected static $actorNamespace = 'zeptech\dynamic\html';

  /* Filesystem path to htdocs. Used to resolve script groups. */
  private $_htdocs;

  public function __construct($outputPath, $htdocs) {
    parent::__construct($outputPath);
    $this->_htdocs = $htdocs;
  }

  protected function getTemplatePath() {
    return __DIR__ . '/htmlProvider.tmpl.php';
  }

  protected function getValues($className) {
    $pageDef = new ReflectionClass($className);
    $page = new Annotations($pageDef);
    if (!isset($page['page'])) {
      throw new Exception("$className is not a page definition");
    }

    $values = array(
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
      $templateDef = new ReflectionClass($templateClass);

      $template = new Annotations($templateDef);
      $values['template'] = $templateClass;
      $tmplDependencies = DependencyParser::parse($templateDef);
      if (count($tmplDependencies) > 0) {
        $values['tmplDependencies'] = $tmplDependencies;
      }

      if (isset($template['title'])) {
        if ($title !== null) {
          $title = $template['title'] . ' - ' . $title;
        } else {
          $title = $template['title'];
        }
      }

      $values['jscripts'] = array_merge(
        $values['jscripts'],
        $template->asArray('script'),
        $this->_resolveScriptGroups($template->asArray('scriptgroup'))
      );

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

    $values['jsPath'] = _P('/js');
    $values['jslibPath'] = _P('/jslib');
    $values['cssPath'] = _P('/css');

    $values['jscripts'] = array_merge(
      $values['jscripts'],
      $page->asArray('script'),
      $this->_resolveScriptGroups($page->asArray('scriptgroup'))
    );
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

      $dependencies = DependencyParser::parse($pageDef);
      $values['dependencies'] = $dependencies;
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

  private function _resolveResources($paths, $relBase) {
    $resolved = array();
    foreach ($paths as $path) {
      if (substr($path, 0, 1) === '/') {
        $resolved[] = _P($path);
      } else {
        $resolved[] = _P("$relBase/$path");
      }
    }
    return $resolved;
  }

  private function _resolveScriptGroups($groups) {
    $scripts = array();
    foreach ($groups as $group) {
      $base = $this->_htdocs;
      if (substr($group, 0, 1) !== '/') {
        $base .= '/js/';
      }

      // Get all suffixed scripts
      echo __DIR__ . "\n";
      echo "Script group: $base$group\n";
      $dirPath = dirname("$base$group");
      echo "Contents of $dirPath:\n";
      if (file_exists($dirPath)) {
        $dir = new DirectoryIterator($dirPath);
        foreach ($dir as $d) {
          echo "  {$d->getPathname()}\n";
        }
      } else {
        echo "$dirPath does not exist";
      }
      foreach (glob("$base$group-*.js") as $script) {
        echo "Adding script $script\n";
        $scripts[] = substr($script, strlen($base));
      }

      // See if a script with the group name and no suffix exists.  This script
      // is added last so any initialization code that relies on the content
      // of the scripts in the group should go in this script.
      if (file_exists("$base$group.js")) {
        $scripts[] = "$group.js";
      }
    }
    return $scripts;
  }
}
