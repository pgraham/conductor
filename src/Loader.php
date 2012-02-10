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
   */
  public static function loadDependencies() {
    if (self::$_loaded) {
      return;
    }
    self::$_loaded = true;

    $libPaths = array(
      'pct' => realpath(__DIR__ . '/../lib/php-code-templates/src/'),
      'reed' => realpath(__DIR__ . '/../../reed/src/'),
      'oboe' => realpath(__DIR__ . '/../../oboe/src/'),
      'clarinet' => realpath(__DIR__ . '/../../clarinet/src/'),
      'bassoon' => realpath(__DIR__ . '/../../bassoon/src/')
    );

    foreach ($libPaths AS $libName => $libPath) {
      if (!file_exists($libPath)) {
        throw new Exception("Unable to find required library $libName." .
          " Expected to find it at: $libPath");
      }
    }

    spl_autoload_register(function ($classname) {
      $parts = explode("\\", $classname);
      $lib = array_shift($parts);

      if (!isset($libPaths[$lib])) {
        return;
      }

      $path = $libPaths[$lib] . implode('/', $parts);
      require $path;
    });
    
  }

}
