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

/**
 * This class encapsulates the process of applying database alters.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AlterDatabaseProcess implements LifecycleProcess
{

	private $db;
	private $schema;
	private $root;

	public function __construct(DatabaseConnection $db, $schema, $root) {
		$this->db = $db;
		$this->schema = $schema;
		$this->root = $root;
	}

	/**
	 * Execute any pending database alters for the conductor, the site and any
	 * installed modules. Order is important.  Site alters are performed first
	 * because they can manipulate the alter versions for conductor and the
	 * modules in order to resolve any conflicts between existing structure and
	 * pending updates.
	 */
	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$this->applySiteAlters($logger);
		$this->applyModuleAlters($logger);
		$this->applyConductorAlters($logger);
	}

	private function applySiteAlters($logger) {
		$qr = $this->db->query(
			"SELECT `value` FROM $this->schema.`config_values`
			WHERE `name` = 'site-alter'"
		);
		$siteVersion = (int) $qr->fetchColumn();
		$siteAlterPath = "$this->root/src/sql";
		if (file_exists($siteAlterPath)) {
			$this->applyDatabaseAlters($siteVersion, $siteAlterPath, $logger);
		}
	}

	private function applyModuleAlters($logger) {
		$modulesPath = "$this->root/modules";
		if (file_exists($modulesPath)) {
			$modules = new DirectoryIterator($modulesPath);
			foreach ($modules as $module) {
				$moduleName = $module->getBasename();

				$qr = $this->db->query(
					"SELECT `value`
					FROM $this->schema.`config_values`
					WHERE `name` = 'module-$moduleName-alter'");

				$moduleVersion = (int) $qr->fetchColumn();
				$moduleAlterPath = "$modulesPath/$moduleName/sql";
				if (file_exists($moduleAlterPath)) {
					$this->applyDatabaseAlters($moduleVersion, $moduleAlterPath, $logger);
				}
			}
		}
	}

	private function applyConductorAlters($logger) {
		$qr = $this->db->query(
			"SELECT `value` FROM $this->schema.`config_values` WHERE `name` = 'cdt-alter'");
		$cdtVersion = (int) $qr->fetchColumn();
		$cdtAlterPath = "$this->root/vendor/zeptech/conductor/resources/sql";
		$this->applyDatabaseAlters($cdtVersion, $cdtAlterPath, $logger);
	}

	private function applyDatabaseAlters($curVersion, $alterPath, $logger) {
		applyDatabaseAlters(
			$this->db->getInfo()->getUsername(),
			$this->db->getInfo()->getPassword(),
			$this->schema,
			$curVersion,
			$alterPath
		);
	}
}
