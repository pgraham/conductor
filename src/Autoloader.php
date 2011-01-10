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
    echo "Loading class $className\n";
  }
}
