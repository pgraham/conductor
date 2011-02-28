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
 * @package conductor/config
 */
namespace conductor\config;

use \SimpleXMLElement;

use \conductor\Exception;

/**
 * This class parses a conductor configuration XML file's <pages> section.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/config
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
    $pages = Array();

    $ns = '';
    if (isset($cfg['nsbase'])) {
      $ns = $cfg['nsbase'];

      if (substr($ns, 0, 1) == '\\') {
        // Since model classes are loaded using dynamic functionality the
        // leading backslash will be implied so remove it for consistency
        $ns = substr($ns, 1);
      }

      if (substr($ns, -1) != '\\') {
        $ns .= '\\';
      }
    }

    foreach ($cfg->page AS $page) {
      if (!isset($page['id'])) {
        throw new Exception('Pages must declare an id');
      }
      $id = $page['id']->__toString();

      if (isset($page['title'])) {
        $title = $page['title']->__toString();
      } else {
        $title = ucfirst($id);
      }

      if (isset($page['class'])) {
        $className = $ns . $page['class'];
      } else {
        $className = $ns . ucfirst($id);
      }

      $pages[$id] = Array
      (
        'title' => $title,
        'class'  => $className
      );
    }

    $default = null;
    if (isset($cfg['default'])) {
      $default = $cfg['default']->__toString();

      if (!array_key_exists($default, $pages)) {
        throw new Exception("Default page ($default) is not defined");
      }
    } else  if (count($pages) > 0) {
      $default = $pages[0]['id'];
    }

    return Array
    (
      'default' => $default,
      'pages'   => $pages
    );
  }

}
