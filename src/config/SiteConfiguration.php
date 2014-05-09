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

use ArrayObject;
use Exception;

/**
 * This class encapsulates a sites runtime configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SiteConfiguration extends ConfigurationSet
{

	// Composite configuration
	private $pathInfo;
	private $namespace;
	private $dbConfig;

	// Path configuration
	private $logDir;
	private $target;

	/**
	 * Load the configuration stored in the specified file.
	 */
	public function __construct($root, $env) {
		parent::__construct($root, $env);

		$config = Configuration::get($root);

		$this->namespace = $this->getValue('namespace', $config, $env);

		$this->pathInfo = new PathConfiguration($root, $env);
		$this->dbConfig = new DatabaseConfiguration($root, $env);

		$this->logDir = $this->getValueOrDefault('logDir', $config, $env, '');
	}

	public function getDbConfig() {
		return $this->dbConfig;
	}

	public function getLogDir() {
		return $this->logDir;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function getPathInfo() {
		return $this->pathInfo;
	}
}
