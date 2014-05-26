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
 * This class encapsulates the process of executing the `bower install` command
 * from a specified directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class BowerInstallStep extends Command
{

	private $target;

	/**
	 * Create an object that will execute the `bower install` command at the
	 * specified directory.
	 *
	 * @param string $target
	 *   The path from which to execute the `bower install` command
	 */
	public function __construct($target) {
		//parent::__construct('bower install', $target);
		$this->target = $target;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$oldCwd = getcwd();
		chdir($this->target);
		passthru('bower install --allow-root');
		chdir($oldCwd);
	}

}
