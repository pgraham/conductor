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

ensureFn('git_tag', 'git_move_tag');

/**
 * This class encapsulates a {@link LifecycleProcess} step that tags the current
 * version of a repository.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TagRepositoryStep implements LifecycleProcess
{

	private $repo;
	private $tag;
	private $force;

	/**
	 * Create a step which tags the HEAD version of the specified repository with
	 * the given tag name.
	 *
	 * @param string $repo
	 *   The path of the repo to tag.
	 * @param string $tag
	 *   The name of the tag
	 * @param boolean $force
	 *   Whether or not to force the tag if another commit is already tagged with
	 *   the same name.
	 */
	public function __construct($repo, $tag, $force = false) {
		$this->repo = $repo;
		$this->tag = $tag;
		$this->force = (bool) $force;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		if ($this->force) {
			git_move_tag($this->repo, $this->tag);
		} else {
			git_tag($this->repo, $this->tag);
		}
	}
}
