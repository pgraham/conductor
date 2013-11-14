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

use \zpt\util\DB;
use \zpt\util\PdoExt;

ensureFn('generatePassword');

/**
 * This class creates the database CRUD users of a Conductor site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInitDbUserCreator {

	private $pdo;
	private $dbCreator;

	private $prodDbUser;
	private $prodDbUserPwd;
	private $prodDbUserCreated = false;

	private $devDbUser;
	private $devDbUserPwd;
	private $devDbUserCreated = false;

	private $stageDbUser;
	private $stageDbUserPwd;
	private $stageDbUserCreated = false;

	public function __construct(
		PdoExt $pdo,
		$sitename,
		CdtInitDbCreator $dbCreator
	) {
		$this->pdo = $pdo;
		$this->dbCreator = $dbCreator;

		$this->prodDbUser = $sitename;
		$this->prodDbUserPwd = generatePassword();

		$this->devDbUser = "{$sitename}_d";
		$this->devDbUserPwd = generatePassword();

		$this->stageDbUser = "{$sitename}_s";
		$this->stageDbUserPwd = generatePassword();
	}

	public function create() {
		$prodDb = $this->dbCreator->getProductionDatabaseName();
		$devDb = $this->dbCreator->getDevelopmentDatabaseName();
		$stageDb = $this->dbCreator->getStagingDatabaseName();

		binLogInfo("Creating production database user $this->prodDbUser");
		$this->createCrudUser($this->prodDbUser, $this->prodDbUserPwd);
		$this->prodDbUserCreated = true;
		binLogInfo("Granting CRUD permission to database $prodDb", 1);
		$this->grantCrudPerms($prodDb, $this->prodDbUser);

		binLogInfo("Creating development database user $this->devDbUser");
		$this->createCrudUser($this->devDbUser, $this->devDbUserPwd);
		$this->devDbUserCreated = true;
		binLogInfo("Granting CRUD permission to database $devDb", 1);
		$this->grantCrudPerms($devDb, $this->devDbUser);

		binLogInfo("Creating staging database user $this->stageDbUser");
		$this->createCrudUser($this->stageDbUser, $this->stageDbUserPwd);
		$this->stageDbUserCreated = true;
		binLogInfo("Granting CRUD permission to database $stageDb", 1);
		$this->grantCrudPerms($stageDb, $this->stageDbUser);
	}

	public function rollback() {
		if ($this->prodDbUserCreated) {
			$this->pdo->dropUser($this->prodDbUser, 'localhost');
		}

		if ($this->devDbUserCreated) {
			$this->pdo->dropUser($this->devDbUser, 'localhost');
		}

		if ($this->stageDbUserCreated) {
			$this->pdo->dropUser($this->stageDbUser, 'localhost');
		}
	}

	private function createCrudUser($username, $password) {
		$this->pdo->createUser($username, $password, 'localhost');
	}

	private function grantCrudPerms($db, $username) {
		$this->pdo->grantUserPermissions($db, $username, DB::CRUD_PERMISSIONS);
	}
}
