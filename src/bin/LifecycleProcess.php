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

/**
 * Interface for classes that encapsulate a process in a site's lifecycle.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface LifecycleProcess
{

	const STAGED_TAG = 'staged';

	/**
	 * Execute the process
	 *
	 * @param LoggerInterface $logger
	 * @return mixed
	 * @throws InvalidArgumentException
	 *   If the process cannot complete because any of its supplied parameters are
	 *   invalid.
	 * @throws RuntimeException
	 *   If the process aborts unexpectedly while processing.
	 */
	public function execute(LoggerInterface $logger = null);

}
