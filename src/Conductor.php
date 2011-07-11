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

use \clarinet\model\Parser as ModelParser;
use \clarinet\ActorFactory;
use \clarinet\Clarinet;
use \clarinet\Criteria;

use \conductor\config\Parser;
use \conductor\script\Client;
use \conductor\script\ServiceProxy;
use \conductor\template\PageTemplate;

use \oboe\head\Javascript;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Conductor {

  const JQUERY_VERSION = '1.5.2';

  /** Conductor configuration values */
  public static $config = null;

  /* Whether or not conductor has been initialized */
  private static $_initialized = false;

  /**
   * Retrieve the configuration value with the given name.  In order for this to
   * work the database must be setup to handle configuration values.
   *
   * @param {string} $name The name of the configuration value to retrieve.
   */
  public static function getConfigValue($name) {
    self::_ensureInitialized();

    $c = new Criteria();
    $c->addEquals('name', $name);

    $persister = ActorFactory::getActor('persister',
      'conductor\model\ConfigValue');
    $rows = $persister->retrieve($c);
    if (count($rows) == 0) {
      return null;
    }

    $obj = $rows[0];
    return $obj->getValue();
  }

  /**
   * Retrieve a list of configuration values that belong to the given group(s).
   * Nested groups can be accessed by provided a dot separated path to the
   * nested group.  Configuration values can be retrieved for several groups at
   * once by providing an array of group names.
   *
   * @param mixed $groups Either a single string group name or an array of
   *   group names.
   * @return A list of ConfigValue instances.
   */
  public static function getConfigValues($groups) {
    if (!is_array($groups)) {
      return self::getConfigValues(array($groups));
    }

    $groupConditions = array();
    foreach ($groups AS $group) {
      $groupConditions[] = "$group.%";
    }

    $c = new Criteria();
    $c->addLike('name', $groupConditions);

    $persister = ActorFactory::getActor('persister',
      'conductor\model\ConfigValue');

    $values = $persister->retrieve($c);
    $idxd = array();
    foreach ($values AS $value) {
      $valName = $value->getName();

      $valName = substr($valName, strpos($valName, '.') + 1);

      $idxd[$valName] = $value;
    }

    return $idxd;
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
      if (self::isDebug()) {
        // TODO - Give a warning if in debug mode
        // TODO - Add logging interface to Reed that can be used for this
      }
      return;
    }
    self::$_initialized = true;

    Loader::loadDependencies(); 

    // Load the site's configuration from the defined/default path
    if ($configPath === null) {
      // The default assumes that conductor is at the following path:
      //   <website-root>/lib/conductor/src/Conductor.php
      $configPath = __DIR__ . '/../../../conductor.cfg.xml';
    }
    self::$config = Parser::parse($configPath);
    $pathInfo = self::$config['pathInfo'];
    Autoloader::$genBasePath = $pathInfo->getTarget() . '/conductor';

    // If a custom autoloader was defined in the configuration, load it now
    if (isset(self::$config['autoloader'])) {
      require_once self::$config['autoloader'];
    }

    // Set options for debug mode.
    if (self::isDebug()) {
      ini_set('display_errors', 'on');
      ini_set('html_errors', 'on');

      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_WARNING, 1);
      assert_options(ASSERT_BAIL, 0);
      assert_options(ASSERT_QUIET_EVAL, 0);
    }

    // Initialize clarinet
    Clarinet::init(array
      (
        'pdo'        => self::$config['pdo'],
        'outputPath' => $pathInfo->getTarget(),
        'debug'      => self::$config['debug']
      )
    );

    // TODO These scripts are included here instead of in load() because
    //      It is possible that a template constructor includes scripts
    //      that rely on jquery or this client.  However, the proper spot
    //      for these includes is in the load function because they are not
    //      needed for aynchronous requests.  So, either the page template
    //      interface needs to be updated to include a facility for retrieving
    //      scripts that are to be added to the page, or the conductor.cfg.xml
    //      parser needs to be updated to support specifying global and page
    //      level scripts
    $jQueryName = 'jquery.min.js';
    if (self::isDebug()) {
      $jQueryName = 'jquery.js';
    }

    $jQuery = new Javascript('http://ajax.googleapis.com/ajax/libs/jquery/'
      . self::JQUERY_VERSION . DIRECTORY_SEPARATOR . $jQueryName);
    $jQuery->addToHead();

    $client = new Client();
    $client->addToPage();

    $service = new ServiceProxy('conductor\Service', $pathInfo);
    $service->addToHead();
  }

  /**
   * Getter for whether or not the site is operating in DEBUG mode.
   *
   * @return boolean
   */
  public static function isDebug() {
    return self::$config['debug'];
  }

  /**
   * Loads the framework.  This is only necessary when handling non-ajax
   * requests.
   *
   * @param PageTemplate $template The PageTemplate for the response.
   */
  public static function load(PageTemplate $template = null) {
    self::_ensureInitialized();

    // Initialize conductor's extensions to oboe\Page and include the conductor
    // client
    Page::init();

    if ($template !== null) {
      Page::setTemplate($template);
    }

    // Authenticate.
    if (isset($_POST['uname']) && isset($_POST['pw'])) {
      Auth::init($_POST['uname'], $_POST['pw']);
    } else {
      Auth::init();
    }
  }

  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Conductor has not yet been initialized');
    }
  }
}
