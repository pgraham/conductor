<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\bin;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use zpt\db\DatabaseConnection;
use zpt\dbup\DatabaseUpdater;
use DirectoryIterator;
use Exception;
use RuntimeException;

/**
 * This class encapsulates the process of applying database alters.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AlterDatabaseProcess implements LifecycleProcess
{

	private $db;
	private $root;

	private $dbup;
	private $dbVerManager;

	public function __construct(DatabaseConnection $db, $root) {
		$this->db = $db;
		$this->root = $root;

		$this->dbVerManager = new CdtDatabaseVersionManager();

		$this->dbup = new DatabaseUpdater();
		$this->dbup->setDatabaseVersionManager($this->dbVerManager);
	}

	/**
	 * Execute any pending database alters for conductor, the site and any
	 * installed modules. Order is important.  Site alters are performed first
	 * because they can manipulate the alter versions for conductor and the
	 * modules in order to resolve any conflicts between existing structure and
	 * pending updates.
	 */
	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}
		$this->dbup->setLogger($logger);

		$schema = $this->db->getInfo()->getSchema();

		try {
			$this->applyConductorAlters($logger);
		} catch (Exception $e) {
			$msg = String("Failed to apply Conductor alters to {0} ({1})")
				->format($schema, $this->root);
			throw new RuntimeException($msg, 0, $e);
		}

		try {
			$this->applyModuleAlters($logger);

		} catch (Exception $e) {
			$msg = String("Failed to apply module alters to {0} ({1})")
				->format($schema, $this->root);
			throw new RuntimeException($msg, 0, $e);
		}

		try {
			$this->applySiteAlters($logger);

		} catch (Exception $e) {
			$msg = String("Failed to apply site alters to {0} ({1})")
				->format($schema, $this->root);
			throw new RuntimeException($msg, 0, $e);
		}

		$logger->notice("Database $schema is up-to-date");
	}

	protected function applyConductorAlters($logger) {
		$this->dbVerManager->setConfigurationProperty('cdt-alter');
		$this->dbup->update($this->db, $this->getCdtAlterPath());
	}

	protected function applyModuleAlters($logger) {
		foreach ($this->getModulesIterator() as $moduleName => $moduleAlterPath) {
			$this->dbVerManager->setConfigurationProperty("module-$moduleName-alter");
			$this->dbup->update($this->db, $moduleAlterPath);
		}
	}

	protected function applySiteAlters($logger) {
		$this->dbVerManager->setConfigurationProperty('site-alter');
		$this->dbup->update($this->db, $this->getSiteAlterPath());
	}

	private function getCdtAlterPath() {
		return "$this->root/vendor/zeptech/conductor/resources/sql";
	}

	private function getModulesIterator() {
		$modulesPath = "$this->root/modules";
		$modules = [];

		if (!file_exists($modulesPath)) {
			return $modules;
		}

		$modulesDir = new DirectoryIterator($modulesPath);
		foreach ($modulesDir as $module) {
			if ($module->isDot()) {
				continue;
			}

			$moduleName = $module->getBasename();
			$modulePath = $module->getPathname();

			$modules[$moduleName] = "$modulePath/sql";
		}
		return $modules;
	}

	private function getSiteAlterPath() {
		return "$this->root/src/sql";
	}
}
