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
  public static function loadDependencies($root, $namespace) {
    if (self::$_loaded) {
      return;
    }
    self::$_loaded = true;

    $lib = "$root/lib";
    $src = "$root/src";
    $cdtLib = "$lib/conductor/lib";
    $target = "$root/target";

    $libPaths = array(
      'reed' => "$lib/reed/src",
      'oboe' => "$lib/oboe/src"
    );

    foreach ($libPaths as $libName => $libPath) {
      if (!file_exists($libPath)) {
        throw new Exception("Unable to find required library $libName." .
          " Expected to find it at: $libPath");
      }
    }

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

    // Class loader for php-annotations, php-code-templates, php-rest-server,
    // clarinet and generated classes
    $annoLdr = new SplClassLoader('zeptech\anno', "$cdtLib/php-annotations");
    $annoLdr->register();

    $pctLdr = new SplClassLoader('zpt\pct', "$cdtLib/php-code-templates");
    $pctLdr->register();

    $restLdr = new SplClassLoader('zeptech\rest', "$cdtLib/php-rest-server");
    $restLdr->register();

    $ormLdr = new SplClassLoader('zeptech\orm', "$cdtLib/clarinet");
    $ormLdr->register();

    $dynLdr = new SplClassLoader('zeptech\dynamic', $target);
    $dynLdr->register();

    // Class loader for site classes
    $siteLdr = new SplClassLoader($namespace, $src);
    $siteLdr->register();

    // Register loaders for the site's modules
    if (file_exists("$root/modules")) {
      $dir = new DirectoryIterator("$root/modules");
      foreach ($dir as $mod) {
        $modName = $mod->getBasename();

        $modLdr = new SplClassLoader("zpt\\mod\\$modName", $mod->getPathName());
        $modLdr->register();
      }
    }
  }
}
