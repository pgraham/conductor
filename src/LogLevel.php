<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt;

/**
 * Provide public access to an array of the Monolog logging levels to allow 
 * lookup when defined in configuration as a string (which makes more sense than 
 * putting a number in a configuration file).
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LogLevel {

	public static $levels = [
		100 => 'DEBUG',
		200 => 'INFO',
		250 => 'NOTICE',
		300 => 'WARNING',
		400 => 'ERROR',
		500 => 'CRITICAL',
		550 => 'ALERT',
		600 => 'EMERGENCY'
	];

}
