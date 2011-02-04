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

    if (isset($cfg['htmldir'])) {
      $pageDir = $cfg['htmldir'];
      if (substr($pageDir, 0, 1) != '/') {
        $pageDir = $pathRoot . '/' . $pageDir;
      }

      if (substr($pageDir, -1) == '/') {
        $pageDir = substr($pageDir, 0, -1);
      }
    } else {
      $pageDir = $pathRoot;
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

      if (isset($page['file'])) {
        $pagePath = $page['file']->__toString();

        if (substr($pagePath, 0, 1) != '/') {
          $pagePath = $pageDir . '/' . $pagePath;
        }
      } else {
        $pagePath = $pageDir . '/' . $id . '.php';
      }

      $pages[$id] = Array
      (
        'title' => $title,
        'file'  => $pagePath
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
