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
 * This class encapsulates an external command to invoke as part of a
 * {@link LifecycleProcess}. The output from the command will be logged used the
 * given logger (or a NullLogger if not provided).
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Command implements LifecycleProcess
{
	private $cmd;
	private $cwd;

	public function __construct($cmd, $cwd = null) {
		$this->cmd = $cmd;
		$this->cwd = $cwd;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$descriptorSpec = [ [ 'pipe', 'r' ], [ 'pipe', 'w' ], [ 'pipe', 'a' ] ];
		$pipes = [];

		$proc = proc_open($this->cmd, $descriptorSpec, $pipes, $this->cwd);
		stream_set_blocking($pipes[2], 0);
		if ($err = stream_get_contents($pipes[2])) {
			throw new RuntimeException(
				"Unable to start process for command `$this->cmd`: $err"
			);
		}

		if (!is_resource($proc)) {
			throw new RuntimeException("Unable to start process for command "
				. "`$this->cmd`");
		}

		$logger->info("Executing command `{cmd}` from {cwd}", [
			'cmd' => $this->cmd,
			'cwd' => $this->cwd ? $this->cwd : getcwd()
		]);

		do {
			$line = fgets($pipes[1]);
			$logger->debug($line);

			$status = proc_get_status($proc);
		} while ($status['running']);
		$logger->debug("Command `$this->cmd` terminated");

		if ($status['exitcode'] > 0) {
			$logger->error($status['exitcode'] . ': ' . stream_get_contents($pipes[2]));
		}

		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
	}

}
