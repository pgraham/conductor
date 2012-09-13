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

use \conductor\jslib\JsLib;
use \conductor\template\PageTemplate;

use \oboe\head\Javascript;
use \oboe\head\Link;
use \oboe\Element;

use \reed\generator\CodeTemplate;
use \reed\ClassLoader;
use \reed\File;

use \zeptech\dynamic\Configurator;
use \zeptech\dynamic\InjectionConfigurator;
use \zeptech\dynamic\ServerConfigurator;
use \zeptech\orm\runtime\Clarinet;
use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;
use \zeptech\rest\RestServer;
use \zpt\cdt\compile\Compiler;
use \zpt\cdt\di\Injector;
use \zpt\cdt\exception\AuthException;

use \Exception;
use \PDO;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Conductor {

  const JQUERY_VERSION = '1.7.1';

  /* Conductor configuration */
  private static $_config;

  /* Whether or not conductor has been initialized */
  private static $_initialized = false;

  /**
   * Retrieve the {@link Configuration} object for the site.
   *
   * @return Configuration
   */
  public static function getConfig() {
    return self::$_config;
  }

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

    $persister = Persister::get('zpt\cdt\model\ConfigValue');
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

  
    $persister = Persister::get('zpt\cdt\model\ConfigValue');
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
   * Getter for the path info associated with conductor config used to
   * initialize this session.
   *
   * @return \reed\WebSitePathInfo
   */
  public static function getPathInfo() {
    self::_ensureInitialized();
    return self::$_config['pathInfo'];
  }

  /**
   * Initialize the framework.  This consists of registering the autoloaders for
   * the libraries, connecting to the database and initializing clarinet.
   *
   * @param string $root The path to the root of the website.
   * @param boolean $authenticate Whether or not to authenticate a session,
   *   default true.  The only time this is false is during a deploy compile.
   */
  public static function init($root, $authenticate = true) {
    if (self::$_initialized) {
      return;
    }
    self::$_initialized = true;

    // Register class loaders for conductor's dependencies
    Loader::registerDependencies($root);

    // Set dev mode configuration and do a compile if the target directory is
    // writeable.
    if (is_writable("$root/target")) {
      ini_set('display_errors', 'on');
      ini_set('html_errors', 'on');
      ini_set('error_log', "$root/target/php.error");

      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_WARNING, 1);
      assert_options(ASSERT_BAIL, 0);
      assert_options(ASSERT_QUIET_EVAL, 0);

      try {
        if (File::dirlock("$root/target")) {
          $compiler = new Compiler();
          $compiler->compile($root);
          File::dirunlock("$root/target");
        } else {
          // This currently shouldn't happen since the only reason File::dirlock
          // would return false is if the target isn't writeable but if that
          // changes this may start to show up in the logs.
          error_log('Unable to compile');
        }
      } catch (Exception $e) {
        File::dirunlock("$root/target");
        throw new Exception($e);
      }
    }

    // Load the site's configuration
    self::$_config = Configurator::getConfig();

    $pathInfo = self::$_config['pathInfo'];
    $namespace = self::$_config['namespace'];

    // Register a class loader for the site's base namespace
    Loader::registerNamespace($namespace, "$root/src");

    // Initiate a database connection
    try {
      $dbConfig = self::$_config['db_config'];

      $driver = $dbConfig['db_driver'];
      $schema = $dbConfig['db_schema'];
      $host = $dbConfig['db_host'];
      $user = $dbConfig['db_user'];
      $pass = $dbConfig['db_pass'];

      $dsn = "$driver:dbname=$schema;host=$host";
      $pdo = new PDO($dsn, $user, $pass);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
      throw new Exception("Unable to connect to database");
    }

    // Initialize clarinet
    Clarinet::init($pdo);

    // Initialize Dependency injection
    $configurator = new InjectionConfigurator();
    $configurator->configure();

    // Authenticate.
    if ($authenticate) {
      Injector::getBean('authProvider')->init();
    }

    // Initialize localization
    // TODO Make the language determination smart.  Should be retrieved from the
    //      following places in order of priority
    //
    //        1. $_GET['lang']
    //        2. $_SESSION['lang']
    //        3. Default: en
    //
    //      If a login has just completed successfully, then the login process
    //      should retrieve the user's preferred language and store it in the
    //      session.  If the language is set in $_GET['lang'] then it will be
    //      stored in the session for future requests.  This means that
    //      $_GET['lang'] will override a user's preferred language for the
    //      current session.
    L10N::load('en', $pathInfo['target']);

    // Now that initialization is finished the request can be processed
    self::processRequest();
  }

  /**
   * Getter for whether or not the site is operating in DEV mode.
   *
   * @return boolean
   */
  public static function isDevMode() {
    self::_ensureInitialized();
    return self::$_config['env'] === 'dev';
  }

  /**
   * Process the request.
   */
  public static function processRequest() {
    // Make sure that a generated mapping configurator exists
    $pathInfo = self::getPathInfo();

    try {
      $server = new RestServer();
      $configurator = new ServerConfigurator();
      $configurator->configure($server);

      $urlInfo = parse_url($_SERVER['REQUEST_URI']);
      $resource = _AbsP($urlInfo['path']);
      $action = $_SERVER['REQUEST_METHOD'];

      if (isset($_SERVER['HTTP_ACCEPT'])) {
        $server->setAcceptType($_SERVER['HTTP_ACCEPT']);
      } else {
        $server->setAcceptType('*/*');
      }

      // Process the request
      $server->handleRequest($action, $resource);

      // Get the response before setting the headers as retrieving the response
      // may add additional headers to the response, e.g. if a Content-Type
      // header is added it will be added at this time.
      $response = $server->getResponse();
      foreach ($server->getResponseHeaders() AS $header) {
        header($header);
      }
      echo $response;

    } catch (AuthException $e) {
      error_log($e->getMessage());
      error_log($e->getTraceAsString());

      header('HTTP/1.1 401 Unauthorized');
      // TODO Add appropriate WWW-Authenticate header
      echo "You are not authorized to $action the requested resource.";

    } catch (Exception $e) {
      error_log($e->getMessage());
      error_log($e->getTraceAsString());
      header('HTTP/1.1 500 Internal Server Error');

      $msg = $e->getMessage();
      if (!$msg) {
        $msg = _L('error.generic');
      }
      echo $msg;
    }
  }

  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Conductor has not yet been initialized');
    }
  }
}