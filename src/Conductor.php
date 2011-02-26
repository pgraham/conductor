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

use \conductor\config\Parser;
use \conductor\template\PageTemplate;
use \clarinet\Clarinet;
use \clarinet\Criteria;
use \reed\Config;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Conductor {

  private static $_initialized = false;

  public static $config = null;

  /**
   * Retrieve the configuration value with the given name.  In order for this to
   * work the database must be setup to handler configuration values.
   *
   * @param {string} $name The name of the configuration value to retrieve.
   */
  public static function getConfigValue($name) {
    $c = new Criteria();
    $c->addEquals('name', $name);

    $rows = Clarinet::get('conductor\model\ConfigValue', $c);
    if (count($rows) == 0) {
      return null;
    }

    $obj = $rows[0];
    return $obj->getValue();
  }

  /**
   * Initialize the framework.  This consists of registering the autoloaders for
   * the libraries, connecting to the database and initializing clarinet.
   *
   * @param string $configPath Optional path to a conductor.cfg.xml file.  If
   *   not provided then a default path is used by assume a common directory
   *   structure.
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
    self::$config = Parser::parse($configPath);

    // Initialize clarinet
    Clarinet::init(Array
      (
        'pdo'        => self::$config['pdo'],
        'outputPath' => self::$config['target']
      )
    );
  }

  public static function setPageTemplate(PageTemplate $template) {
    self::_ensureInitialized();

    Page::setTemplate($template);
  }

  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Conductor has not yet been initialized');
    }
  }
}
