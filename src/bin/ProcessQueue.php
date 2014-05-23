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
 * This class composes multiple {@link LifecycleProcess}es in a sequence. If any
 * of the processes in the sequence fail then the process is aborted.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ProcessQueue implements LifecycleProcess
{

	private $queue = [];

	public function add(LifecycleProcess $process) {
		$this->queue[] = $process;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		foreach ($this->queue as $process) {
			$result = $process->execute($logger);
			if ($result === false) {
				return false;
			}
		}
		return true;
	}
}
