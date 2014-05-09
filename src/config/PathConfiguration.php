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

use ReflectionProperty;

/**
 * This class encapsulates a site's runtime path configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PathConfiguration extends ConfigurationSet
{

	private $webRoot;
	private $cdtRoot;
	private $src;
	private $target;
	private $htdocs;

	public function __construct($root, $env) {
		parent::__construct($root, $env);

		// Expose the ConfigurationSet's $root property as array accessible
		$rootProperty = new ReflectionProperty('zpt\cdt\config\ConfigurationSet', 'root');
		$rootProperty->setAccessible(true);
		$this->arrayMembers['root'] = $rootProperty;

		$config = Configuration::get($root);

		$this->webRoot = $this->getValueOrDefault('webRoot', $config, $env, '/');
		$this->cdtRoot = "$root/vendor/zeptech/conductor";
		$this->src = "$root/src";
		$this->target = "$root/target";
		$this->htdocs = "$root/htdocs";
	}
}
