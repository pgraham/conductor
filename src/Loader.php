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
namespace conductor;

/**
 * This class is responsible for loading any PHP libraries installed along side
 * Conductor.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Loader {

  /* Whether or not dependencies have been loaded or not */
  private static $_loaded = false;

  /**
   * Verify that conductor dependencies are installed and register their
   * autoloaders.
   */
  public static function loadDependencies() {
    if (self::$_loaded) {
      return;
    }
    self::$_loaded = true;

    $libPath = __DIR__ . '/../..';
    $loader = new Loader($libPath);
    $loader
      ->load('reed')
      ->load('oboe')
      ->load('clarinet')
      ->load('bassoon');
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  /* The base path for requested libraries. */
  private $_libPath;

  /**
   * Create a new loader instance for the given library path.
   *
   * @param string $libPath Base path from which to load libraries.
   */
  protected function __construct($libPath) {
    $this->_libPath = $libPath;
  }

  /**
   * Load the library with the given name.
   *
   * @param string $lib The name of the library to load.
   * @throws conductor\Exception If the requested library is not found.
   */
  public function load($lib) {
    $libPath = "{$this->_libPath}/$lib";

    if (!file_exists($libPath) || !is_dir($libPath)) {
      throw $this->_libraryNotFoundException($lib);
    }

    $autoloader = "$libPath/src/Autoloader.php";
    if (!file_exists($autoloader)) {
      throw $this->_autoloaderNotFoundException($lib, $autoloader);
    }

    require_once $autoloader;

    return $this;
  }

  /* Create a LibraryNotFoundException */
  private function _libraryNotFoundException($lib) {
    $msg = "The requested library ($lib) is not installed at {$this->_libpath}";
    return new Exception($msg);
  }

  /* Create an AutoloaderNotFoundException */
  private function _autoloaderNotFoundException($lib, $autoloader) {
    $msg = "Could not find autoloader for ($lib) is was expected to be found"
      . " at: $autoloader";
    return new Exception($msg);
  }
}
