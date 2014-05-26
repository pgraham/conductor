<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\bin;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This class encapsulates the process of running the `npm install` command for
 * a new site or one of it's dependencies.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class NpmInstallStep implements LifecycleProcess
{

	private $target;

	/**
	 * Create an object that will execute the `npm install` command at the
	 * specified directory.
	 *
	 * @param string $target
	 *   The path from which to execute the `npm install` command
	 */
	public function __construct($target) {
		$this->target = $target;
		//parent::__construct('npm install', $target);
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$oldCwd = getcwd();
		chdir($this->target);
		passthru('npm install');
		chdir($oldCwd);
	}
}
