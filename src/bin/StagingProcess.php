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
use InvalidArgumentException;
use RuntimeException;

/**
 * This class encapsulates the process for deploying a development site to
 * a local staging environment.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class StagingProcess implements LifecycleProcess
{

	private $source;
	private $target;

	private $db;
	private $stagingDb;
	private $productionDb;
	private $wsLink;
	private $curProd;

	/**
	 * Initialize a new staging process that deploys the site rooted at the
	 * specified source directory to the specified target directory.
	 *
	 * @param string $source
	 *   Root of the development site to deploy.
	 * @param string $target
	 *   Target path of the deployed site.
	 */
	public function __construct($source, $target) {
		$this->source = $source;
		$this->target = $target;
	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$logger->info("Staging site from $this->source to $this->target");
		$this->verifyParameters($logger);

		$queue = new ProcessQueue();
		$queue->add(new ExportProcess($this->source, $this->target));
		$queue->add(new CompileProcess($this->target));

		if ($this->curProd !== null) {
			$queue->add(new CopyUserContentProcess($this->curProd, $this->target));
		}

		$queue->add(new CopyDatabaseProcess(
			$this->db,
			$this->productionDb,
			$this->stagingDb
		));
		$queue->add(new AlterDatabaseProcess(
			$this->db,
			$this->stagingDb,
			$this->target
		));
		$queue->add(new UpdateWebServerLinkProcess($this->wsLink, $this->target));

		$result = $queue->execute($logger);
		if (!$result) {
			// TODO Should the target directory be cleaned up if it was created?
		}

		return $result;
	}

	public function setDatabaseConnection(DatabaseConnection $db) {
		$this->db = $db;
	}

	public function setStagingDatabase($stagingDatabase) {
		$this->stagingDb = $stagingDatabase;
	}

	public function setProductionDatabase($productionDatabase) {
		$this->productionDb = $productionDatabase;
	}

	public function setWebServerLink($webServerLink) {
		$this->wsLink = $webServerLink;
	}

	public function setCurrentProductionPath($currentProductionPath) {
		$this->curProd = $currentProductionPath;
	}

	private function verifyParameters($logger) {
		if (file_exists($this->target)) {
			// Make sure target doesn't already exist
			// TODO Should the target be overwritten should the user be prompted for
			//      this decision?
			throw new InvalidArgumentException("Specified target directory already exists: "
				. $this->target);
		}

		if (!is_createable($this->target)) {
			throw new InvalidArgumentException(
				"Insufficient permissions to create target directory $this->target"
			);
		}

		if ($this->wsLink && !file_exists($this->wsLink)) {
			if (!is_createable($this->wsLink)) {
				throw new InvalidArgumentException(
					"Unable to create the specified webserver link."
				);
			} else {
				$logger->warning(
					"The specified webserver link does not currently exist and will be "
					. "created. You may need to configure your webserver to serve the "
					. "staged site from:\n\n  $this->wsLink\n"
				);
			}
		}

	}

}
