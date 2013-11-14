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

use \zpt\dbup\DatabaseVersionRetrievalScheme;
use \zpt\util\db\DatabaseException;
use \PDO;

/**
 * Conductor zpt\dbup\DatabaseVersionRetrievalScheme implementation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtDatabaseVersionRetrievalScheme
	implements DatabaseVersionRetrievalScheme
{

	private $property = 'cdt-alter';

	/**
	 * Retrieves the value in the `value` column from the row with a specified
	 * value in the `name` column from the table `config_values`.
	 *
	 * @param PDO $db
	 * @return integer
	 */
	public function getVersion(PDO $db) {
		$stmt = $db->prepare('SELECT value FROM config_values WHERE name = :name');

		try {
			$stmt->execute([ 'name' => $this->property ]);
		} catch (DatabaseException $e) {
			if ($e->tableDoesNotExist()) {
				return 0;
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
	 * Set the name of of configuration property which holds the database version.
	 *
	 * @param string $name
	 */
	public function setConfigurationProperty($property) {
		$this->property = $property;
	}

}
