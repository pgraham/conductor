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
 * This class encapsulates the process of updating symlink which has been
 * pointed to by the webserver in order to server content to point to
 * a different site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class UpdateWebServerLinkProcess implements LifecycleProcess
{

	private $wsLink;
	private $target;

	public function __construct($wsLink, $target) {
		$this->wsLink = $wsLink;
		$this->target = $target;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$linkTarget = "$this->target/target/htdocs";
		$logger->info("Creating symlink from $linkTarget to $this->wsLink");
		atomicSymlink($this->wsLink, $linkTarget);
	}
}
