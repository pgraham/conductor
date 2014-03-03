<?php
/**
 * =============================================================================
 * Copyright (c) 2014, Philip Graham
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
namespace zpt\cdt\config;

use zpt\db\DatabaseConnection;

/**
 * This class encapsulates a site's runtime database configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DatabaseConfiguration
{

	private $driver;
	private $host;
	private $username;
	private $password;
	private $schema;

	public function __construct($xmlCfg) {
		if (!isset($xmlCfg->db)) {
			throw new ConfigurationException('No database configuration found');
		}

		if (!isset($xmlCfg->db->username)) {
			throw new ConfigurationException('No database username specified');
		}

		if (!isset($xmlCfg->db->password)) {
			throw new ConfigurationException('No database password specified');
		}

		if (!isset($xmlCfg->db->schema)) {
			throw new ConfigurationException('No database schema specified');
		}

		$this->driver = (isset($xmlCfg->db->driver))
			? (string) $xmlCfg->db->driver
			: 'mysql';

		$this->host = (isset($xmlCfg->db->host))
			? (string) $xmlCfg->db->host
			: 'localhost';

		$this->username =  (string) $xmlCfg->db->username;
		$this->password = (string) $xmlCfg->db->password;
		$this->schema = (string) $xmlCfg->db->schema;
	}

	public function asArray() {
		$dbConfig = array();
		$dbConfig['db_driver'] = $this->driver;
		$dbConfig['db_host'] = $this->host;
		$dbConfig['db_user'] = $this->username;
		$dbConfig['db_pass'] = $this->password;
		$dbConfig['db_schema'] = $this->schema;

		return $dbConfig;
	}

	public function connect($override = []) {
		return new DatabaseConnection(array_merge([
			'driver'   => $this->driver,
			'host'     => $this->host,
			'username' => $this->username,
			'password' => $this->password,
			'schema'   => $this->schema
		], $override));
	}

	public function getDriver() {
		return $this->driver;
	}
}
