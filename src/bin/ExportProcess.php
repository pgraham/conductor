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
 * This class encapsulates the process for exporting a site to a local or remote
 * directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ExportProcess implements LifecycleProcess
{

	private $source;
	private $target;

	/**
	 * Create a new process that exports the site root at the specified source
	 * directory to the specified target directory.
	 *
	 * @param string $source
	 * @param string $target
	 */
	public function __construct($source, $target) {
		$this->source = $source;
		$this->target = $target;
	}

	/**
	 * Execute the export process.
	 */
	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$logger->info("Exporting site from $this->source to $this->target");
		$exportCmd = String(__DIR__ . "/../../bin/cdt-export {0} {1}")
			->format($this->source, $this->target);

		$failure = false;
		passthru($exportCmd, $failure);
		if ($failure) {
			throw new RuntimeException("Unable to export site.");
		}
	}

}
