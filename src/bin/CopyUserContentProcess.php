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
 * This class encapsulates the process for copying the user content of a site to
 * another site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CopyUserContentProcess implements LifecycleProcess
{

	private $source;
	private $target;

	public function __construct($source, $target) {
		$this->source = $source;
		$this->target = $target;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$usrRelDirs = [ 'target/usr', 'target/htdocs/usr' ];
		foreach ($usrRelDirs as $usrRelDir) {
			$usrSrc = "$this->source/$usrRelDir";
			$usrTgt = "$this->target/$usrRelDir";
			if (file_exists($usrSrc)) {
				$logger->info("Copying user content from $usrSrc to $usrTgt");
				passthru("cp -a $usrSrc $usrTgt");
			} else {
				$logger->warning("User content $usrSrc does not exist");
				$logger->info("Creating user content directory: $usrTgt");
				mkdir($usrTgt, 0775, true);
			}
			$logger->debug("Giving web server user (www-data) write access to user " .
				"content directory: $usrTgt");
			chgrp($usrTgt, 'www-data');
		}
	}
}
