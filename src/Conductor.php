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

use \conductor\admin\Template as AdminTemplate;
use \conductor\config\Parser;
use \clarinet\Clarinet;
use \Reed\Config;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Conductor {

  private static $_initialized = false;

  private static $_config = null;

  /**
   * Initialize the framework.  This consists of registering the autoloaders for
   * the libraries for which paths have been provided.
   *
   * The configuration options are given as a key-value array.
   *
   * Options:
   * --------
   *
   * libpath
   *   Base path for Reed, Oboe, Bassoon and Clarinet.  This is a
   *   shortcut to providing individual paths.  The individual libraries will be
   *   assumed to be in a sub directory of the given path with a lower cased
   *   name of the library.  This option can be overridden for individual
   *   libraries by specifying a libraries path as part of the configuration.
   *
   * clarinetconfig
   *   Configuration array to be passed to clarinet's intialization function
   */
  public static function init($configPath = null) {
    if (self::$_initialized) {
      if (defined('DEBUG') && DEBUG === true) {
        // TODO - Give a warning if DEBUG is defined and set to true
        // TODO - Add logging interface to Reed that can be used for this
      }
      return;
    }
    self::$_initialized = true;

    // TODO - If any of these files don't exist output a better error message
    $libPath = __DIR__ . '/../../';
    require_once $libPath . '/reed/src/Autoloader.php';
    require_once $libPath . '/oboe/src/Autoloader.php';
    require_once $libPath . '/bassoon/src/Autoloader.php';
    require_once $libPath . '/clarinet/src/Autoloader.php';

    // Load the site's configuration from the defined/default path
    if ($configPath === null) {
      // The default assumes that conductor is at the following path:
      //   <website-root>/lib/conductor/src/Conductor.php
      $configPath = __DIR__ . '/../../../conductor.cfg.xml';
    }
    
    self::$_config  = Parser::parse($configPath);

    Clarinet::init(Array
      (
        'pdo'        => self::$_config['pdo'],
        'outputPath' => self::$_config['target']
      )
    );
  }

  public static function loadAdmin() {
    self::_ensureInitialized();

//    $template = new AdminTemplate();
  }

  public static function loadPage() {
    self::_ensureInitialized();
  }

  public static function setConfig(Config $config) {
    self::_ensureInitialized();

    Config::setConfig($config);
  }

  public static function setPageTemplate(Template $template) {
    self::_ensureInitialized();

    Page::setTemplate($template);
  }

  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Conductor has not yet been initialized');
    }
  }
}
