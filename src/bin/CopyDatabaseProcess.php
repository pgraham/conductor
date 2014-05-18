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
use zpt\db\DatabaseConnection;

/**
 * The class encapsulates the process for copying on database schema to another.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CopyDatabaseProcess implements LifecycleProcess
{

	private $db;
	private $source;
	private $target;

	public function __construct(
		DatabaseConnection $db,
		$sourceSchema,
		$targetSchema
	) {
		$this->db = $db;
		$this->source = $sourceSchema;
		$this->target = $targetSchema;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		try {
			$logger->info("Coping database $this->source to $this->target");
			//$db->getAdminAdapter()->copyDatabase($this->source, $this->target);
		} catch (RuntimeException $e) {
			$logger->error("Unable to clone database $this->source to $this->target: "
				. $e->getMessage());
		}
	}
}
