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

use \zpt\util\PdoExt;

/**
 * This class is used to create the databases of a Conductor site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInitDbCreator {

	private $pdo;
	private $sitename;

	private $prodDbName;
	private $devDbName;
	private $stageDbName;

	private $prodDbCreated = false;
	private $devDbCreated = false;
	private $stageDbCreated = false;

	public function __construct(PdoExt $pdo, $sitename) {
		$this->pdo = $pdo;
		$this->sitename = $sitename;

		$this->prodDbName = $sitename;
		$this->devDbName = "{$sitename}_d";
		$this->stageDbName = "{$sitename}_s";
	}

	public function create() {
		binLogInfo("Creating production DB $this->prodDbName");
		$this->pdo->createDatabase($this->prodDbName, DB::UTF8);
		$this->prodDbCreated = true;

		binLogInfo("Creating development DB $this->devDbName");
		$this->pdo->createDatabase($this->devDbName, DB::UTF8);
		$this->devDbCreated = true;

		binLogInfo("Creating staging DB $this->stageDbName");
		$this->pdo->createDatabase($this->stageDbName, DB::UTF8);
		$this->stageDbCreated = true;
	}

	public function rollback() {
		if ($this->prodDbCreated) {
			$this->pdo->dropDatabase($this->prodDbName);
		}

		if ($this->devDbCreated) {
			$this->pdo->dropDatabase($this->devDbName);
		}

		if ($this->stageDbCreated) {
			$this->pdo->dropDatabase($this->stageDbName);
		}
	}

	public function getProductionDatabaseName() {
		return $this->prodDbName;
	}

	public function getDevelopmentDatabaseName() {
		return $this->devDbName;
	}

	public function getStagingDatabaseName() {
		return $this->stageDbName;
	}
}
