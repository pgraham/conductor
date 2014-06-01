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
 * This class encapsulates a lifecycle step that either creates or updates
 * a symlink to point to a new target.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SymlinkUpdateStep implements LifecycleProcess
{

	private $link;
	private $target;

	public function __construct($link, $target) {
		$this->link = $link;
		$this->target = $target;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$logger->info("Creating symlink from $this->link to $this->target");
		atomicSymlink($this->link, $this->target);
	}
}
