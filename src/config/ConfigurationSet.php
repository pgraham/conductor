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

use ArrayAccess;
use DomainException;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Base class for classes that encapsulate a subset of the site's configuration
 * defined in conductor.yml.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class ConfigurationSet implements ArrayAccess
{

	protected $root;
	protected $env;

	protected $arrayMembers = [];

	public function __construct($root, $env) {
		$this->root = $root;
		$this->env = $env;

		$reflect = new ReflectionClass($this);
		$privateProps = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);
		foreach ($privateProps as $prop) {
			$prop->setAccessible(true);
			$this->arrayMembers[$prop->getName()] = $prop;
		}
	}

	public function asArray() {
		$a = [];
		foreach ($this->arrayMembers as $name => $prop) {
			$a[$name] = $prop->getValue($this);
		}
		return $a;
	}

	public function getEnv() {
		return $this->env;
	}

	public function getRoot() {
		return $this->root;
	}

	protected function getValue($name, $config, $env) {
		if (isset($config['env']) &&
		    isset($config['env'][$env]) &&
		    isset($config['env'][$env][$name])
		) {
			return $config['env'][$env][$name];
		}

		if (isset($config[$name])) {
			return $config[$name];
		}

		throw new DomainException("Missing configuration value $name");
	}

	protected function getValueOrDefault($name, $config, $env, $default) {
		try {
			return $this->getValue($name, $config, $env);
		} catch (DomainException $e) {
			return $default;
		}
	}

	/*
	 * ===========================================================================
	 * ArrayAccess: Expose concrete private properties as array indexes.
	 * ===========================================================================
	 */

	public function offsetExists($offset) {
		return isset($this->arrayMembers[$offset]);
	}

	public function offsetGet($offset) {
		return $this->offsetExists($offset)
			? $this->arrayMembers[$offset]->getValue($this)
			: null;
	}

	public function offsetSet($offset, $value) {
		throw new OutOfBoundsException("Configuration objects are read only");
	}

	public function offsetUnset($offset) {
		throw new OutOfBoundsException("Configuration objects are read only");
	}
}
