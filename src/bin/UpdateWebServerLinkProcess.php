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
 * This class encapsulates the process of updating a symlink which is used by
 * the web server to serve content.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class UpdateWebServerLinkProcess extends SymlinkUpdateStep
	implements LifecycleProcess
{

	/**
	 * Create a process object which updates the specified link to point to the
	 * given target. This process is atomic.
	 *
	 * @param string $wsLink The path of the symlink to update.
	 * @param string $target The path to which the symlink will point.
	 */
	public function __construct($wsLink, $target) {
		parent::__construct($wsLink, "$target/target/htdocs");
	}
}
