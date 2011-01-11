<?php
namespace Conductor;

class Autoloader {

  /* This is the base path where the Conductor source files are found. */
  public static $basePath = __DIR__;

  /**
   * Autoload function for Conductor class files.
   *
   * @param {string} The name of the class to load.
   */
  public static function loadClass($className) {
    $pathComponents = explode("\\", $className);

    // Make sure this is a Conductor class
    $base = array_shift($pathComponents);
    if ($base != 'Conductor') {
      return;
    }

    $logicalPath = implode('/', $pathComponents);
    $fullPath = self::$basePath.'/'.$logicalPath.'.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    }
  }
}

spl_autoload_register(array('Conductor\Autoloader', 'loadClass'));
