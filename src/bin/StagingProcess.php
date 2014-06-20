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
	 *   Target path of the deployed site. The site will be exported to
	 *   a subdirectory of this path named for the time the site was staged.
	 */
	public function __construct($source, $target) {
		$this->source = $source;
		$this->target = $target;

	}

	public function execute(LoggerInterface $logger = null) {
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$ts = time();
		$exportTarget = "$this->target/$ts";

		$logger->info("Staging site from $this->source to $exportTarget");
		$this->verifyParameters($logger, $exportTarget);

		$queue = new ProcessQueue();
		$queue->add(new ExportProcess($this->source, $exportTarget));
		$queue->add(new CompileProcess($exportTarget));

		if ($this->curProd !== null) {
			// The current production link should be pointing to
			// <production-root>/target/htdocs
			$productionPath = realpath(realpath($this->curProd) . '/../..');
			$queue->add(new CopyUserContentProcess($productionPath, $exportTarget));
		}

		$queue->add(new CopyDatabaseProcess(
			$this->db,
			$this->productionDb,
			$this->stagingDb
		));
		$queue->add(new AlterDatabaseProcess(
			$this->db,
			$this->stagingDb,
			$exportTarget
		));
		$queue->add(new TagStagedVersionStep($this->source, "v{$ts}s"));
		$queue->add(new UpdateWebServerLinkProcess($this->wsLink, $exportTarget));

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

	public function setCurrentProductionLink($currentProductionLink) {
		$this->curProd = $currentProductionLink;
	}

	private function verifyParameters($logger, $exportTarget) {
		if (file_exists($exportTarget)) {
			// Make sure target doesn't already exist
			// TODO Should the target be overwritten should the user be prompted for
			//      this decision?
			throw new InvalidArgumentException("Specified target directory already exists: "
				. $exportTarget);
		}

		if (!is_createable($exportTarget)) {
			throw new InvalidArgumentException(
				"Insufficient permissions to create target directory $exportTarget"
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
