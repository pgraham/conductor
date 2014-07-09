<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt;

/**
 * This class encapsulates information about an enviroment.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Env
{
	const DEV = 'dev';
	const STAGE = 'stage';
	const PROD = 'prod';

	public static $ENVS = [ self::DEV, self::STAGE, self::PROD ];

	private static $cache = [];

	public static function get($env) {
		if (!self::verifyEnv($env)) {
			throw new InvalidEnvironmentException($env);
		}

		if (!isset(self::$cache[$env])) {
			self::$cache[$env] = new Env($env);
		}
		return self::$cache[$env];
	}

	/**
	 * Verify that the given environment type is valid.
	 *
	 * @param string $env
	 * @return boolean
	 */
	public static function verifyEnv($env) {
		return in_array($env, self::$ENVS);
	}

	/* ------------------------------------------------------------------------ */

	private $env;

	protected function __construct($env) {
		$this->env = $env;
	}

	public function __toString() {
		return $this->env;
	}

	public function getName() {
		return $this->env;
	}

	public function is($env) {
		if (is_object($env)) {
			return ($env instanceof Env) && $env === $this;
		} else {
			return $env === $this->env;
		}
	}

}
