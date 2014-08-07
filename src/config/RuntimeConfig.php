<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\config;

/**
 * Interface for generated configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface RuntimeConfig {

	/**
	 * Return an array containing all of the runtime configuration.
	 *
	 * @return array
	 */
	public function getConfig();

	/**
	 * Return an array containing the runtime values of configurable paths.
	 * This SHOULD be the same array as the 'pathInfo' key of the array return by
	 * {@link #getConfig()}.
	 *
	 * @return array
	 */
	public function getPathInfo();

	/**
	 * Getter for the configured site namespace.
	 *
	 * @return string
	 */
	public function getNamespace();

	/**
	 * Getter for the runtime environment.
	 *
	 * @return string
	 */
	public function getEnvironment();

	/**
	 * Getter for the PSR-4 source directory that contains dynamically generated
	 * class files. This SHOULD be the same {@link zpt\opal\Psr4Dir} as the
	 * 'dynTarget' key of the array return by {@link #getConfig()}.
	 *
	 * @return zpt\opal\Psr4Dir
	 */
	public function getDynamicClassTarget();
}
