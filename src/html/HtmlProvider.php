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
namespace conductor\html;

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
      'actor' => str_replace('\\', '_', $className)
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

    if (isset($page['font'])) {
      $fonts = is_array($page['font'])
        ? $page['font']
        : array($page['font']);

      $values['fonts'] = implode('|', str_replace(' ', '+', $fonts));
    }

    if (isset($page['css'])) {
      $sheets = $page['css'];
      if (!is_array($sheets)) {
        $sheets = array($sheets);
      }

      $values['sheets'] = array();
      foreach ($sheets AS $css) {
        if (substr($css, 0, 1) === '/') {
          $values['sheets'][] = $asWebPath($css);
        } else {
          $values['sheets'][] = $asWebPath("/css/$css");
        }
      }
    }

    if (isset($page['script'])) {
      $jscripts = $page['script'];
      if (!is_array($page['script'])) {
        $jscripts = array($page['script']);
      }

      $values['jscripts'] = array();
      foreach ($jscripts as $js) {
        if (substr($js, 0, 1) === '/') {
          $values['jscripts'][] = $asWebPath($js);
        } else {
          $values['jscripts'][] = $asWebPath("/js/$js");
        }
      }
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
}
