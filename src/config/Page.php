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
namespace conductor\config;

use \conductor\resources\FontResource;
use \conductor\Exception;
use \conductor\ResourceSet;
use \SimpleXMLElement;

/**
 * This class parses a conductor configuration XML file's <pages> section.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Page {

  /**
   * Parses the pages defined in a conductor.cfg.xml file.
   *
   * @param SimpleXMLElement $cfg Object representing the <pages> node of the
   *   configuration file.
   * @param string $pathRoot The base path for any relative paths defined in the
   *   configuration.
   * @return array Array representation of the given configuration.
   */
  public static function parse(SimpleXMLElement $cfg, $pathRoot) {
    $pages = array();

    $ns = '';
    if (isset($cfg['nsbase'])) {
      // Trim any backslashes since model classes are loaded using dynamic
      // functionality so any leading backslash will be implied and a trailing
      // backslash is added automatically.
      $ns = trim($cfg['nsbase'], '\\') . '\\';
    }

    foreach ($cfg->page AS $pageXml) {
      if (!isset($pageXml['id'])) {
        throw new Exception('Pages must declare an id');
      }
      $id = $pageXml['id']->__toString();
      $page = new Page($pageXml);

      if (isset($pageXml['title'])) {
        $title = $pageXml['title']->__toString();
      } else {
        $title = ucfirst($id);
      }
      $page->_title = $title;

      if (isset($pageXml['class'])) {
        $className = $ns . $pageXml['class'];
      } else {
        $className = $ns . ucfirst($id);
      }
      $page->_className = $className;

      if (isset($pageXml['theme'])) {
        $page->_theme = (string) $pageXml['theme'];
      }

      if (isset($pageXml->js['ns'])) {
        $page->_jsNs = (string) $pageXml->js['ns'];
      }

      $pages[$id] = $page;
    }

    $default = null;
    if (isset($cfg['default'])) {
      $default = $cfg['default']->__toString();

      if (!array_key_exists($default, $pages)) {
        throw new Exception("Default page ($default) is not defined");
      }
    } else if (count($pages) > 0) {
      reset($pages);
      $default = key($pages);
    }

    return array
    (
      'default' => $default,
      'pages'   => $pages
    );
  }

  /* Parse a ResourceSet from a <page> SimpleXMLElement. */
  private function _parseResources(SimpleXMLElement $xmlCfg) {
    $resources = new ResourceSet();
    if (isset($xmlCfg->css)) {
      $sheets = array();
      foreach ($xmlCfg->css->sheet AS $sheet) {
        $sheets[] = (string) $sheet;
      }
      $resources->addSheets($sheets);
    }

    if (isset($xmlCfg->js)) {
      $scripts = array();
      foreach ($xmlCfg->js->script AS $script) {
        $scripts[] = (string) $script;
      }
      $resources->addScripts($scripts);
    }

    if (isset($xmlCfg->imgs)) {
      $imgs = array();
      foreach ($xmlCfg->imgs->img AS $img) {
        $imgs[] = (string) $img;
      }
      $resources->addImages($imgs);
    }

    if (isset($xmlCfg->jslibs)) {
      $jslibs = array();
      foreach ($xmlCfg->jslibs->jslib AS $jslib) {
        $className = "conductor\\jslib\\$jslib[name]";
        $opts = array();
        foreach ($jslib->children() AS $opt) {
          $opts[$opt->getName()] = (string) $opt;
        }
        $jslibs[] = new $className($opts);
      }
      $resources->addJsLibs($jslibs);
    }

    if (isset($xmlCfg->fonts)) {
      $fonts = array();
      foreach ($xmlCfg->fonts->font AS $fontXml) {
        $font = new FontResource((string) $fontXml);
        if (isset($fontXml['variants'])) {
          $font->setVariants((string) $fontXml['variants']);
        }
        $fonts[] = $font;
      }
      $resources->addFonts($fonts);
    }

    if (isset($xmlCfg->services)) {
      $srvcs = array();
      foreach ($xmlCfg->services->service AS $srvc) {
        $srvcs[] = (string) $srvc['class'];
      }
      $resources->addServices($srvcs);
    }

    return $resources;
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  /* The name of the class which provides the HTML fragment for the page */
  private $_className;

  /*
   * If the page contains javascript, it should be placed into it's own
   * namespace.  Declaring the namespace in configuration means that the
   * javascript can rely on the namespace being declared making it easier to 
   * spead the site's javascript across multiple files.
   */
  private $_jsNs;

  /* The declared resources required by the page. */
  private $_resources;

  /* An optional jQuery UI theme used by the page. */
  private $_theme;

  /* The page's title */
  private $_title;

  /* The SimpleXMLElement containing the page's configuration. */
  private $_xmlCfg;

  /**
   * Page configuration object's can't be instantiated directly and need to be
   * parsed using static parse(SimleXMLElement, string) method.
   */
  protected function __construct($xmlCfg) {
    $this->_xmlCfg = $xmlCfg;
  }

  public function getClassName() {
    return $this->_className;
  }

  public function getJsNs() {
    return $this->_jsNs;
  }

  public function getResources() {
    if ($this->_resources === null) {
      $this->_resources = self::_parseResources($this->_xmlCfg);
    }
    return $this->_resources;
  }

  public function getTheme() {
    return $this->_theme;
  }

  public function getTitle() {
    return $this->_title;
  }
}
