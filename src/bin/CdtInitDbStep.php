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
use \Exception;
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
		'dbuser' => 'postgres'
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

			binLogInfo("Applying initial schema to development database");
			$pdo->beginTransaction();

			// Apply database alters
			$dbup = new DatabaseUpdater();
			$dbup->setLogger(new CmdLnPsrLoggerImpl());

			$versionRetriever = new CdtDatabaseVersionRetrievalScheme();
			$dbup->setDatabaseVersionRetrievalScheme($versionRetriever);

			binLogInfo("Connecting to development database", 1);
			$db = $pdo->newConnection([ 'database' => "{$ns}_d" ]);

			binLogInfo("Applying alters", 1);
			$dbup->update($db, "$baseDir/vendor/zeptech/conductor/resources/sql");

			$pdo->commit();
			binLogSuccess("Done.");
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

			throw $e;
		}
	}

	private function applyDefaultOptions(array $opts) {
		$opts = array_merge([], self::$defaultOpts, $opts);

		if (!empty($opts['dbpasswd'])) {
			$dbpasswd = $opts['dbpasswd'];
		} else {
			if (!empty($opts['interactive'])) {
				$dbpasswd = passwordPrompt(" $opts[dbuser] DB password: ");
			} else {
				throw new Exception("Database password must be provided for non-interactive init");
			}
		}
		$opts['dbpasswd'] = $dbpasswd;
		return $opts;
	}

	private function connectToDb(array $opts) {
		return new PdoExt([
			'driver'            => $opts['dbdriver'],
			'host'              => $opts['dbhost'],
			'username'          => $opts['dbuser'],
			'password'          => $opts['dbpasswd'],
			'pdoAttributes'     => [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]
		]);
	}

}
