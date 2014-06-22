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
use RuntimeException;

/**
 * This class encapsulates a {@link LifecycleProcess} step that tags the current
 * version of the specified repository as the staged version.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TagStagedVersionStep implements LifecycleProcess
{

	private $repo;
	private $buildVersion;

	/**
	 * Create a new LifecycleProcess step that will tag a staged version of the
	 * repository. The HEAD version of the specified repository is assigned two
	 * tags: {@link LifecycleProcess::STAGED_TAG}, which will be moved if it
	 * already exists, and a given version.
	 *
	 * @param string $repo
	 *   The repository that is being staged.
	 * @param string $version
	 *   A unique version with which to tag the staged version.
	 * @throws RuntimeException
	 *   If a tag the same as the given version string already exists.
	 */
	public function __construct($repo, $version) {
		$this->repo = $repo;
		$this->buildVersion = $version;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$queue = new ProcessQueue();
		$queue->add(new TagRepositoryStep(
			$this->repo,
			LifecycleProcess::STAGED_TAG,
			true /* Force, move any existing tag with the same name */
		));
		$queue->add(new TagRepositoryStep(
			$this->repo,
			$this->buildVersion
		));
		$queue->execute($logger);
	}

}
