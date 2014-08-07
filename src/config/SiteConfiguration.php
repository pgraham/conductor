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

use zpt\opal\Psr4Dir;
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
	private $logLevel;
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
		$this->logLevel = $this->getValueOrDefault(
			'logLevel',
			$config,
			$env,
			'NONE'
		);
	}

	public function getDbConfig() {
		return $this->dbConfig;
	}

	public function getDynamicClassTarget() {
		return new Psr4Dir($this->pathInfo['dyn']['target'], $this->pathInfo['dyn']['prefix']);
	}

	public function getLogDir() {
		return $this->logDir;
	}

	public function getLogLevel() {
		return $this->logLevel;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function getPathInfo() {
		return $this->pathInfo;
	}
}
