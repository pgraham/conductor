<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NullHandler;
use Monolog\Processor\PsrLogMessageProcessor;

use dyn\RuntimeConfigLoader;
use dyn\InjectionConfigurator;
use dyn\ServerConfigurator;
use zpt\cdt\compile\SiteCompiler;
use zpt\cdt\config\SiteConfiguration;
use zpt\cdt\di\Injector;
use zpt\cdt\exception\AuthException;
use zpt\cdt\exception\TopLevelDebugExceptionHandler;
use zpt\cdt\rest\AuthExceptionHandler;
use zpt\cdt\rest\InjectedRestServer;
use zpt\cdt\rest\LocalizedDefaultExceptionHandler;
use zpt\cdt\rest\LocalizedRestExceptionHandler;
use zpt\cdt\rest\PdoExceptionHandler;
use zpt\cdt\rest\ValidationExceptionHandler;
use zpt\db\DatabaseConnection;
use zpt\db\exception\DatabaseException;
use zpt\opal\Psr4Dir;
use zpt\orm\Clarinet;
use zpt\orm\Criteria;
use zpt\orm\Repository;
use zpt\util\File;
use zpt\util\DirectoryLockTimeoutException;
use Exception;

/**
 * The main interface for Conductor setup.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Conductor {

	const JQUERY_VERSION = '2.0.3';

	/* Conductor configuration */
	private static $_config;

	/* Whether or not conductor has been initialized */
	private static $_initialized = false;

	private static $applicationLogger;

	/**
	 * Retrieve the {@link Configuration} object for the site.
	 *
	 * @return Configuration
	 */
	public static function getConfig() {
		return self::$_config;
	}

	/**
	 * Retrieve the configuration value with the given name.	In order for this to
	 * work the database must be setup to handle configuration values.
	 *
	 * @param {string} $name The name of the configuration value to retrieve.
	 */
	public static function getConfigValue($name) {
		self::_ensureInitialized();

		$c = new Criteria();
		$c->addEquals('name', $name);

		$obj = Clarinet::getOne('zpt\cdt\model\ConfigValue', $c);
		if ($obj !== null) {
			return $obj->getValue();
		}
		return null;
	}

	/**
	 * Retrieve a list of configuration values that belong to the given group(s).
	 * Nested groups can be accessed by provided a dot separated path to the
	 * nested group.	Configuration values can be retrieved for several groups at
	 * once by providing an array of group names.
	 *
	 * @param mixed $groups Either a single string group name or an array of
	 *	 group names.
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

		return Clarinet::getAll('zpt\cdt\model\ConfigValue', function ($entity) {
			$name = $entity->getName();
			return substr($name, strpos($name, '.') + 1);
		}, $c);
	}

	/**
	 * Getter for the path info associated with conductor config used to
	 * initialize this session.
	 *
	 * @return array
	 */
	public static function getPathInfo() {
		self::_ensureInitialized();
		return self::$_config['pathInfo'];
	}

	/**
	 * Initialize the framework.	This consists of registering the autoloaders for
	 * the libraries, connecting to the database and initializing clarinet.
	 *
	 * @param string $root The path to the root of the website.
	 * @param object $loader Composer loader -- see htdocs/srvr.php
	 */
	public static function init($root, $loader) {
		if (self::$_initialized) {
			return;
		}
		self::$_initialized = true;

		// Register class loaders for conductor's dependencies
		Loader::registerDependencies($root, $loader);

		// Set dev mode configuration and do a compile if the target directory is
		// writeable.
		$isDevMode = is_writable("$root/target");
		if ($isDevMode) {
			ini_set('display_errors', 'on');
			ini_set('html_errors', 'on');
			ini_set('error_log', "$root/target/php.error");

			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 1);
			assert_options(ASSERT_BAIL, 0);
			assert_options(ASSERT_QUIET_EVAL, 0);

			// Add a dev mode exception handler and error handler
			$exceptionHandler = new TopLevelDebugExceptionHandler();
			set_exception_handler(array($exceptionHandler, 'handleException'));
			set_error_handler(array($exceptionHandler, 'handleError'));

			if (isset($_GET['clean'])) {
				$dirs = array( 'i18n', 'zeptech', 'zpt', 'generated', 'htdocs/css',
				               'htdocs/img', 'htdocs/js', 'htdocs/jslib', 'htdocs/lib');
				$files = array( 'php.error', 'cdt.log' );

				foreach ($dirs as $dir) {
					passthru("rm -r $root/target/$dir");
				}

				foreach ($files as $file) {
					passthru("rm $root/target/$file");
				}
			}

			try {
				if (File::dirlock("$root/target", isset($_GET['forceunlock']))) {

					$log = new Logger('compile');
					$log->pushHandler(new RotatingFileHandler(
						"$root/target/compile.log",
						Logger::INFO,
						1 /* only keep most recent file */
					));

					$cfg = new SiteConfiguration($root, 'dev');
					$compiler = new SiteCompiler();
					$compiler->setLogger($log);
					$compiler->compile($cfg, $loader);
					File::dirunlock("$root/target");
				} else {
					// This currently shouldn't happen since the only reason File::dirlock
					// would return false is if the target isn't writeable but if that
					// changes this may start to show up in the logs.
					error_log('Unable to compile');
				}
			} catch (DirectoryLockTimeoutException $e) {

				// Since this is dev mode send a response which will allow the user to
				// forcefully unlock the directory
				$url = $_SERVER['REQUEST_URI'];
				if (strpos($url, '?') !== false) {
					$url .= '&forceunlock';
				} else {
					$url .= '?forceunlock';
				}

				echo "<!DOCTYPE html>\n",
						 "<html lang=en><head><meta charset=utf-8 /><title>Force unlock</title>\n",
						 "<body><h1>Unable to aquire target lock</h1>\n",
						 "<p>If a previous request encountered an error durring the compilation process then the target lock will not have been released. If you've recently fixed a compilation problem and believe that this is the issue then you can ",
						 "<a href=\"$url\">forcefully remove the lock and continue</a></p>\n";
				exit;

			} catch (Exception $e) {
				File::dirunlock("$root/target");
				throw new Exception(
					"An exception occured while compiling the site",
					0,
					$e
				);
			}
		}

		// Load the site's configuration
		self::$_config = RuntimeConfigLoader::loadConfig();
		self::$_config['dynTarget']->registerWith($loader);

		$pathInfo = self::$_config['pathInfo'];
		$namespace = self::$_config['namespace'];

		// Register a class loader for the site's base namespace. This is only
		// necessary when not running in dev mode because the compilation process
		// will already have added this otherwise.
		if (!$isDevMode) {
			$loader->add($namespace, "$root/src");
		}

		// Initiate a database connection
		try {
			$dbConfig = self::$_config['db_config'];

			$driver = $dbConfig['driver'];
			$schema = $dbConfig['schema'];
			$host = $dbConfig['host'];
			$user = $dbConfig['username'];
			$pass = $dbConfig['password'];

			// TODO Use zpt\db\DatabaseConnection instead
			$db = new DatabaseConnection([
				'driver' => $driver,
				'host' => $host,
				'username' => $user,
				'password' => $pass,
				'schema' => $schema
			]);
			Injector::addBean('db', $db);

		} catch (DatabaseException $e) {
			throw new Exception("Unable to connect to database");
		}

		// Initialize clarinet
		$orm = new Repository($db);
		Injector::addBean('orm', $orm);

		// Initialize Dependency injection
		$configurator = new InjectionConfigurator();
		$configurator->configure();

		// Configure application logger
		$logger = Injector::getBean('logger');
		$cfgLogLevel = self::$_config['logLevel'];
		$logLevel = 0;
		if ($cfgLogLevel !== 'NONE') {
			$logLevel = array_search($cfgLogLevel, LogLevel::$levels);
		}
		if ($logLevel > 0) {
			$handler = new StreamHandler($pathInfo['target'] . '/cdt.log', $logLevel);
			$logger->pushHandler($handler);
		} else {
			$logger->pushHandler(new NullHandler());
		}
		self::$applicationLogger = $logger;

		// Initialize localization
		// TODO Make the language determination smart.	Should be retrieved from the
		//			following places in order of priority
		//
		//				1. $_GET['lang']
		//				2. $_SESSION['lang']
		//				3. Default: en
		//
		//			If a login has just completed successfully, then the login process
		//			should retrieve the user's preferred language and store it in the
		//			session.	If the language is set in $_GET['lang'] then it will be
		//			stored in the session for future requests.	This means that
		//			$_GET['lang'] will override a user's preferred language for the
		//			current session.
		L10N::load('en', $pathInfo['target']);

		// Now that initialization is finished the request can be processed
		self::processRequest($pathInfo);
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
	 * Getter for whether or not the site is operating in PRODUCTION mode.
	 *
	 * @return boolean
	 */
	public static function isProductionMode() {
		self::_ensureInitialized();
		return self::$_config['env'] === 'prod';
	}

	/**
	 * Process the request.
	 */
	public static function processRequest($pathInfo) {
		$server = new InjectedRestServer($pathInfo);
		$server->setLogger(self::$applicationLogger);

		$server->registerExceptionHandler(
			'Exception',
			new LocalizedDefaultExceptionHandler()
		);
		$server->registerExceptionHandler(
				'zpt\rest\RestException',
				new LocalizedRestExceptionHandler(
						$server->getExceptionHandler('zpt\rest\RestException')
				)
		);
		$server->registerExceptionHandler(
			'zpt\cdt\exception\AuthException',
			new AuthExceptionHandler()
		);
		$pdoExceptionHandler = new PdoExceptionHandler(
			Injector::getBean('companionLoader')
		);
		$server->registerExceptionHandler(
			'zpt\orm\PdoExceptionWrapper',
			$pdoExceptionHandler
		);
		$server->registerExceptionHandler(
			'zeptech\orm\runtime\ValidationException',
			new ValidationExceptionHandler()
		);
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
	}

	private static function _ensureInitialized() {
		if (!self::$_initialized) {
			throw new Exception('Conductor has not yet been initialized');
		}
	}

	private static function logException(Exception $e) {
		$msg = $e->getMessage();
		$code = $e->getCode();
		$prev = $e->getPrevious();

		if ($code) {
			error_log("$code: $msg");
		} else {
			error_log($msg);
		}
		error_log($e->getTraceAsString());

		if ($prev) {
			error_log("Caused by:");
			self::logException($prev);
		}

	}
}
