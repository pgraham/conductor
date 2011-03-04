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

// This is the only spot in any of the woodwinds project's where code appears
// outside of a class.  A planned compiler for conductor will remove this when
// preparing a site for production
if (!defined('DEBUG')) {
  define('DEBUG', true);
  ini_set('display_errors', 'on');
  ini_set('html_errors', 'on');
}

/**
 * Autoloader for conductor classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Autoloader {

  /* This is the base path where the Conductor source files are found. */
  public static $basePath = __DIR__;

  /**
   * Autoload function for Conductor class files.
   *
   * @param {string} The name of the class to load.
   */
  public static function loadClass($className) {
    if (substr($className, 0, 10) != 'conductor\\') {
      return;
    }

    $logicalPath = str_replace('\\', '/', substr($className, 10));
    $fullPath = self::$basePath.'/'.$logicalPath.'.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    }
  }
}
spl_autoload_register(array('conductor\Autoloader', 'loadClass'));
