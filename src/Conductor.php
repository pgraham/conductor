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

use \conductor\jslib\JsLib;
use \conductor\resources\BaseResources;
use \conductor\resources\JsAppResources;
use \conductor\template\PageTemplate;

use \oboe\head\Javascript;
use \oboe\head\Link;
use \oboe\Element;

use \reed\Generator\CodeTemplate;
use \reed\ClassLoader;
use \reed\File;

use \Exception;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Conductor {

  const JQUERY_VERSION = '1.7.1';

  /** @deprecated Conductor configuration values */
  public static $config = null;

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
   * Getter for the hostname on which the website is running.
   *
   * @return string
   */
  public static function getHostName() {
    return self::$_config->getHostName();
  }

  /**
   * Return the page configuration for the page with the given id or the default
   * page if no id is provided.
   *
   * @return config\PageConfiguration
   */
  public static function getPage($pageId = null) {
    return self::$_config->getPage($pageId);
  }


  /**
   * Getter for the path info associated with conductor config used to
   * initialize this session.
   *
   * @return \reed\WebSitePathInfo
   */
  public static function getPathInfo() {
    self::_ensureInitialized();
    return self::$_config->getPathInfo();
  }

  /**
   * Getter for the configured time that an inactive session remains valid.
   *
   * @return integer
   */
  public static function getSessionTtl() {
    return self::$_config->getAuthConfiguration()->getSessionTtl();
  }

  /**
   * Initialize the framework.  This consists of registering the autoloaders for
   * the libraries, connecting to the database and initializing clarinet.
   *
   * @param string $configPath Optional path to a conductor.cfg.xml file.
   * @param boolean $authenticate Whether or not to authenticate a session,
   *   default true.  The only time this is false is during compilation.
   */
  public static function init($configPath, $authenticate = true) {
    if (self::$_initialized) {
    }
    self::$_initialized = true;

    Loader::loadDependencies(); 

    // Load the site's configuration from the defined/default path
    if ($configPath === null || !file_exists($configPath)) {
      throw new Exception("No config file specified");
    }
    self::$_config = Configuration::parse($configPath);

    $pathInfo = self::$_config->getPathInfo();
    Autoloader::$genBasePath = $pathInfo->getTarget() . '/conductor';

    // If a site specific namespace was specified register a classloader for
    // site source classes.
    if (self::$_config->getSiteNamespace() !== null) {
      ClassLoader::register($pathInfo->getSrcPath(),
        self::$_config->getSiteNamespace());
    }

    // Set options for debug mode.
    if (self::isDevMode()) {
      ini_set('display_errors', 'on');
      ini_set('html_errors', 'on');

      $errorLog = File::joinPaths($pathInfo->getTarget(), '/php.error');
      ini_set('error_log', $errorLog);

      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_WARNING, 1);
      assert_options(ASSERT_BAIL, 0);
      assert_options(ASSERT_QUIET_EVAL, 0);
    }

    // Initialize clarinet
    Clarinet::init(self::$_config->getClarinetConfiguration());

    // Authenticate.
    if ($authenticate) {
      Auth::init();
    }
  }

  /**
   * Getter for whether or not the site is operating in DEV mode.
   *
   * @return boolean
   */
  public static function isDevMode() {
    self::_ensureInitialized();
    return self::$_config->isDevMode();
  }

  /**
   * Loads the framework.  This is only necessary when handling non-ajax
   * requests.
   *
   * @param PageTemplate $template The PageTemplate for the response.
   */
  public static function load($page = null, $template = null) {
    self::_ensureInitialized();
    $pathInfo = Conductor::getPathInfo();

    // Initialize conductor's extensions to oboe\Page
    Page::init();

    // Get the configuration for the requested (or default) page
    $pageCfg = self::$_config->getPage($page);

    // All pages contain a script which declares necessary namespaces and
    // provides functions which give access to path into.  This script is
    // built here - TODO - Make this a template so it can be compiled when the
    // site is deployed
    // -------------------------------------------------------------------------
    $baseJsOutPath = $pathInfo->getTarget() . '/base.js';
    if (self::isDevMode()) {
      $jsParams = array(
        'rootPath' => $pathInfo->getWebRoot()
      );

      // If the site's javascript is encapsulated in a module, add a script
      // which
      // deplares the module
      $jsNs = $pageCfg->getJsNs();
      if ($jsNs !== null) {
        $jsParams['jsns'] = $jsNs;
      }

      $baseJsSrcPath = $pathInfo->getLibPath() .
        '/conductor/src/resources/js/base.tmpl.js';
      CodeTemplate::compile($baseJsSrcPath, $baseJsOutPath, $jsParams);
    }
    Element::js()->add(file_get_contents($baseJsOutPath))->addToHead();

    // Include resources
    // -------------------------------------------------------------------------
    $resources = new BaseResources($pageCfg->getTheme());
    if ($template !== null) {
      $resources = $resources->merge(
        self::$_config->getTemplate($template)->getResources());
      Page::setTemplate($template);
    }
    $resources = $resources->merge($pageCfg->getResources());

    $resources->inc($pathInfo, self::$_config->isDevMode());
  }

  /**
   * Include resources that provide support for building a javascript app.
   */
  public static function loadJsAppSupport($theme = null) {
    $opts = null;
    if ($theme !== null) {
      $opts = array('theme' => $theme);
    }
    JsLib::includeLib(JsLib::JQUERY_UI, $opts);

    $appSupport = new JsAppResources();
    if (self::isDevMode()) {
      $appSupport->compile();
    }
    $appSupport->inc();
  }

  /**
   * This function loads the default page and dumps it.
   *
   * This function should only be called while processing a synchronous request.
   * See {@link PageLoader::loadPage} for loading page content in response to
   * an asynchronous request.
   */
  public static function loadDefaultPage() {
    self::loadPage();
  }

  /**
   * This function loads the page with the given name and dumps it.
   *
   * This function should only be called while processing a synchronous request.
   * See {@link PageLoader::loadPage} for loading page content in response to
   * an asynchronous request.
   */
  public static function loadPage($page = null) {
    PageLoader::loadPage($page)->addToBody();
    Page::dump(PageLoader::getPageTitle($page));
  }

  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Conductor has not yet been initialized');
    }
  }
}
