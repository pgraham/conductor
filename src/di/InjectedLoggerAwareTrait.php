<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\di;

use Psr\Log\LoggerInterface;

/**
 * Trait for classes that use Conductor's application logger as an injected
 * dependency.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
trait InjectedLoggerAwareTrait
{

	/** @Injected */
	protected $logger;

	/**
	 * Sets a logger.
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}
