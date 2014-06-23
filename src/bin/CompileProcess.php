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
use zpt\cdt\compile\SiteCompiler;

/**
 * This class encapsulates the process for compiling a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CompileProcess implements LifecycleProcess
{

	private $root;
	private $env;

	public function __construct($root, $env = SiteCompiler::ENV_STAGE) {
		$this->root = $root;
		$this->env = $env;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$logger->info("Compiling site rooted at $this->root");
		$compileCmd = String("{0}/vendor/bin/cdt-compile {1}")
			->format($this->root, $this->env);

		$failure = false;
		passthru($compileCmd, $failure);
		if ($failure) {
			$logger->error("Unable to compile site");
			return false;
		}
		return true;
	}
}
