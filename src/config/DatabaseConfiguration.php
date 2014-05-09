<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\config;

use zpt\db\DatabaseConnection;

/**
 * This class encapsulates a site's runtime database configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DatabaseConfiguration extends ConfigurationSet
{

	private $driver;
	private $host;
	private $username;
	private $password;
	private $schema;

	public function __construct($root, $env) {
		parent::__construct($root, $env);

		$config = Configuration::get($root);

		$db = $this->getValue('db', $config, $env);

		$this->driver = $this->getValueOrDefault('driver', $db, $env, 'mysql');
		$this->host = $this->getValueOrDefault('host', $db, $env, 'localhost');
		$this->username = $this->getValue('username', $db, $env);
		$this->password = $this->getValue('password', $db, $env);
		$this->schema = $this->getValue('schema', $db, $env);
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
