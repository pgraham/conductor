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

use zpt\db\DatabaseConnection;
use zpt\dbup\DatabaseVersionManager;
use zpt\util\db\DatabaseException;

/**
 * Conductor zpt\dbup\DatabaseVersionManager implementation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtDatabaseVersionManager
	implements DatabaseVersionManager
{

	private $property = 'cdt-alter';

	/**
	 * Retrieves the value in the `value` column from the row with a specified
	 * value in the `name` column from the table `config_values`.
	 *
	 * @param DatabaseConnection $db
	 * @return integer
	 */
	public function getCurrentVersion(DatabaseConnection $db) {
		$stmt = $db->prepare('SELECT value FROM config_values WHERE name = :name');

		try {
			$stmt->execute([ 'name' => $this->property ]);
		} catch (DatabaseException $e) {
			if ($e->tableDoesNotExist()) {
				return null;
			} else {
				throw $e;
			}
		}

		$version = $stmt->fetchColumn();
		if ($version === false) {
			$version = null;
		}
	}

	/**
	 * Sets the value in the `value` column from the row with a specified value in
	 * the `name` column of the `config_values` table.
	 *
	 * @param DatabaseConnection $db
	 * @param integer $version
	 */
	public function setCurrentVersion(DatabaseConnection $db, $version) {
		if ($version === 1) {
			$stmt = $db->prepare('INSERT INTO config_values (name, value)
				VALUES (:name, :val)');
		} else {
			$stmt = $db->prepare('UPDATE config_values SET value = :val
				WHERE name = :name');
		}
		$stmt->execute([ 'name' => $this->property, 'val' => $version ]);
	}

	/**
	 * Set the name of of configuration property which holds the database version.
	 *
	 * @param string $name
	 */
	public function setConfigurationProperty($property) {
		$this->property = $property;
	}

}
