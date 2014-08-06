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
   * @param object $loader Composer loader
   */
  public static function registerDependencies($root, $loader) {
    if (self::$_loaded) {
      return;
    }
    self::$_loaded = true;

    $lib = "$root/lib";
    $cdt = "$lib/conductor";
    $cdtLib = "$cdt/lib";
    $target = "$root/target";

    $optLibs = array(
      'pdf' => 'php-pdf'
    );

    foreach ($optLibs as $optLib => $optLibPath) {
      if (file_exists("$lib/$optLibPath")) {
        $loader->add("zpt\\$optLib", "$lib/$optLibPath");
      }
    }

    // Class loader for generated classes
    // TODO Capture this namespace in a Psr4Dir instance so that is can be
    // shared thoughout the request
    $loader->setPsr4('dyn\\', "$target/generated");
    $loader->add('zpt\dyn', "$target/generated");

    // Register loaders for the site's modules
    if (file_exists("$root/modules")) {
      $dir = new DirectoryIterator("$root/modules");
      foreach ($dir as $mod) {
        $modName = $mod->getBasename();

        $loader->add("zpt\\mod\\$modName", $mod->getPathName());
      }
    }
  }
}
