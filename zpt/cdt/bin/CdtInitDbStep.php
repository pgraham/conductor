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
namespace zpt\cdt\bin;

use \zpt\dbup\DatabaseUpdater;
use \zpt\util\PdoExt;
use \DirectoryIterator;
use \PDO;

ensureFn('generatePassword');
ensureFn('passwordPrompt');

/**
 * This class is used to initialize the database of a Conductor site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInitDbStep {

	private static $defaultOpts = [
		'interactive' => true,
		'dbdriver' => 'pgsql',
		'dbhost' => 'localhost',
		'dbuser' => 'root'
	];

	public function execute($baseDir, $ns, $opts) {
		binLogHeader("Initializing Conductor Databases");
		$opts = $this->applyDefaultOptions($opts);

		$pdo = $this->connectToDb($opts);
		binLogSuccess("Connected to DB.");

		try {
			$dbCreator = new CdtInitDbCreator($pdo, $ns);
			$dbCreator->create();

			$dbUserCreator = new CdtInitDbUserCreator($pdo, $ns, $dbCreator);
			$dbUserCreator->create();

			$pdo->beginTransaction();

			// Apply database alters
			$versionRetriever = new CdtDatabaseVersionRetrievalScheme();
			$dbup = new DatabaseUpdater();
			$dbup->setCurrentVersionRetrievalScheme($versionRetriever);
			$db = $pdo->newConnection([ 'database' => "{$ns}_d" ]);

			$versionRetriever->setConfigurationPropertyName('site-alter');
			$dbup->update($db, "$baseDir/src/sql");

			if (file_exists("$baseDir/modules")) {
				$modules = new DirectoryIterator("$baseDir/modules");
				foreach ($modules as $module) {
					$moduleName = $module->getBasename();

					$versionRetriever->setConfigurationPropertyName(
						"module-$moduleName-alter"
					);
					$dbup->update($db, "$baseDir/modules/$moduleName/sql");
				}
			}

			$versionRetriever->setConfigurationPropertyName('cdt-alter');
			$dbup->update($db, "$baseDir/vendor/zeptech/conductor/resources/sql");

			$pdo->commit();
		} catch (Exception $e) {
			if ($pdo->inTransaction()) {
				$pdo->rollback();
			}

			if (isset($dbUserCreator)) {
				$dbUserCreator->rollback();
			}

			if (isset($dbCreator)) {
				$dbCreator->rollback();
			}
		}
	}

	private function applyDefaultOptions(array $opts) {
		array_merge([], self::$defaultOpts, $opts);

		if (!empty($opts['dbpasswd'])) {
			$dbpasswd = $opts['dbpasswd'];
		} else {
			if ($opts['interactive']) {
				$dbpasswd = passwordPrompt("$dbuser DB password: ");
			} else {
				throw new Exception("Database password must be provided for non-interactive init");
			}
		}
	}

	private function connectToDb(array $opts) {
		return new PdoExt([
			'driver'            => $opts['dbdriver'],
			'host'              => $opts['dbhost'],
			'username'          => $opts['username'],
			'password'          => $opts['password'],
			'pdoAttributes'     => [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]
		]);
	}

}
