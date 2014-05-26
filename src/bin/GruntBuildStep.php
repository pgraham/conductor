<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\bin;

/**
 * This class encapsulates the process of executing the `grunt` command
 * from a specified directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class GruntBuildStep extends Command
{

	/**
	 * Create an object that will execute the `grunt` command at the
	 * specified directory.
	 *
	 * @param string $target
	 *   The path from which to execute the `grunt` command
	 */
	public function __construct($target) {
		parent::__construct('grunt', $target);
	}

}
