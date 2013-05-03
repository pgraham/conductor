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
namespace zpt\cdt;

use \DirectoryIterator;
use \Exception;
use \SplClassLoader;

/**
 * This class is responsible for loading any PHP libraries installed along side
 * Conductor.
 *
 * TODO Update dependency directory structures to comply with PSR-0
 *      https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Loader {

  /* Whether or not dependencies have been loaded or not */
  private static $_loaded = false;

  /**
   * Verify that conductor dependencies are installed and register their
   * autoloaders.
   *
   * @param string $root Root path for the website
   * @param string $namespace Site source namespace
   */
  public static function registerDependencies($root) {
    if (self::$_loaded) {
      return;
    }
    self::$_loaded = true;

    $lib = "$root/lib";
    $cdt = "$lib/conductor";
    $cdtLib = "$cdt/lib";
    $target = "$root/target";

    // Register zeptech autoloaders
    $libPaths = array(
      'oboe' => "$cdtLib/oboe/src"
    );

    foreach ($libPaths as $libName => $libPath) {
      if (!file_exists($libPath)) {
        throw new Exception("Unable to find required library $libName." .
          " Expected to find it at: $libPath");
      }
    }

    // Register class loaders for dependencies that follow legacy package
    // structure
    spl_autoload_register(function ($classname) use ($libPaths) {
      $parts = explode("\\", $classname);
      $lib = array_shift($parts);

      if (!isset($libPaths[$lib])) {
        return;
      }

      $path = $libPaths[$lib] . '/' . implode('/', $parts) . '.php';
      if (file_exists($path)) {
        require $path;
      }
    });

    $optLibs = array(
      'pdf' => 'php-pdf'
    );

    foreach ($optLibs as $optLib => $optLibPath) {
      if (file_exists("$lib/$optLibPath")) {
        self::registerNamespace("zpt\\$opLib", "$lib/$optLibPath");
      }
    }

    // Class loader for php-annotations, php-code-templates, php-rest-server,
    // clarinet and generated classes
    self::registerNamespace('zeptech\rest', "$cdtLib/php-rest-server");
    self::registerNamespace('zpt\dyn', $target);

    // Register loaders for the site's modules
    if (file_exists("$root/modules")) {
      $dir = new DirectoryIterator("$root/modules");
      foreach ($dir as $mod) {
        $modName = $mod->getBasename();

        self::registerNamespace("zpt\\mod\\$modName", $mod->getPathName());
      }
    }

    // Load primitive wrapper functions
    require_once "$cdtLib/reed/zpt/util/prim-wrap.php";
  }

  public static function registerNamespace($namespace, $src, $debug = false) {
    $ldr = new SplClassLoader($namespace, $src, $debug);
    $ldr->register();
  }
}
