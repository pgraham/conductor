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

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \SimpleXMLElement;

/**
 * This class parses the models section of a conduction.cfg.xml file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/config
 */
class Model {

  /**
   * Parse the model configuration in order to create a list of fully qualified
   * classnames to use as model classes.
   *
   * @param SimpleXMLElement $cfg Model configuration object.
   * @param string $pathRoot The base path for any relative paths defined in the
   *                           configuration.
   * @return array List of fully qualified class names
   */
  public static function parse(SimpleXMLElement $cfg, $pathRoot) {
    $models = Array();
    if (isset($cfg['scandir'])) {
      if (substr($cfg['scandir'], 0, 1) == '/') {
        $scanDir = $cfg['scandir'];
      } else {
        $scanDir = $pathRoot . '/' . $cfg['scandir'];
      }

      if (substr($scanDir, -1) == '/') {
        $scanDir = substr($scanDir, 0, -1);
      }

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


      // For all php files in the scanned directory.  The classname is derieved
      // as $ns\directory-path-relative-to-scandir\filename-without-extension
      $i = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanDir));
      foreach ($i AS $file) {
        if ($file->isDir()) {
          continue;
        }

        $realPath = $file->getRealPath();
        if (substr($realPath, -4) != '.php') {
          continue;
        }

        $relPath = str_replace($scanDir, '', $realPath);

        // Transform the path into a relative namespace by replacing directory
        // separators with backslashes and removing the extension
        $subNs = str_replace('/', '\\', substr($relPath, 0, -4));

        if (substr($subNs, 0, 1) == '\\') {
          $subNs = substr($subNs, 1);
        }

        $fullyQualified = $ns . $subNs;
        $models[] = $fullyQualified;
      }
    }

    if (isset($cfg->model)) {
      foreach ($cfg->model AS $model) {
        $models[] = $model;
      }
    }

    return $models;
  }
}
