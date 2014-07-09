<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\exception;

use InvalidArgumentException;

/**
 * Exception class thrown when trying to instantiate an {@link zpt\cdt\Env} 
 * instance for an unrecognized environment type.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class InvalidEnvironmentException extends InvalidArgumentException
{

	private $env;

	public function __construct($env) {
		parent::__construct("Unrecognized environment: $env");
		$this->env = $env;
	}

	public function getEnvironment() {
		return $this->env;
	}
}
