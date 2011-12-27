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
 * @package conductor
 */
namespace conductor;

/**
 * Autoloader for conductor classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Autoloader {

  /* This is the base path for generated source files */
  public static $genBasePath = null;

  /* This is the base path where the Conductor source files are found. */
  private static $_basePath = __DIR__;

  /**
   * Autoload function for Conductor class files.
   *
   * @param {string} The name of the class to load.
   */
  public static function loadClass($className) {
    if (substr($className, 0, 10) !== 'conductor\\') {
      return;
    }

    $logicalPath = str_replace('\\', '/', substr($className, 10));
    $fullPath = self::$_basePath.'/'.$logicalPath.'.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    } else if (self::$genBasePath !== null) {
      $genFullPath = self::$genBasePath . '/' . $logicalPath . '.php';
      if (file_exists($genFullPath)) {
        require_once $genFullPath;
      }
    }
  }
}
spl_autoload_register(array('conductor\Autoloader', 'loadClass'));
