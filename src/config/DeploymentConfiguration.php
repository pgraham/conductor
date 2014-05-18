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

/**
 * This class encapsulates the deployment configuration for a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DeploymentConfiguration extends ConfigurationSet
{

	private $path;
	private $wsLink;

	public function __construct($root, $env) {
		parent::__construct($root, $env);

		$config = Configuration::get($root);

		$deploy = $this->getValue('deploy', $config, $env);

		$this->path = $this->getValueOrDefault('path', $deploy, $env, null);
		$this->wsLink = $this->getValueOrDefault('wsLink', $deploy, $env, null);
	}
}
