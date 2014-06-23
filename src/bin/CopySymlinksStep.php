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
use DirectoryIterator;

/**
 * This class encapsulates a deployment steps that copies any symlinks from the
 * currently deployed site's document root to the new site's document root.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CopySymlinksStep implements LifecycleProcess
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

		$logger->info("Copying symlinks from $this->source to $this->target");
		$srcDir = new DirectoryIterator($this->source);
		foreach ($srcDir as $f) {
			if (!$f->isLink()) {
				continue;
			}

			// Get the target of the link
			$linkName = $f->getFilename();
			$linkTarget = readlink($f->getPathname());
			
			// Create a link from target/htdocs to the link target
			$logger->debug("Linking from $linkTarget to $this->target/$linkName");
			symlink($linkTarget, "$this->target/$linkName");
		}
	}
}
