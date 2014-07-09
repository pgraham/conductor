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
use zpt\cdt\Env;
use zpt\db\DatabaseConnection;
use InvalidArgumentException;
use RuntimeException;

/**
 * This class encapsulates the process of deploying a development site to
 * production.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DeployProcess implements LifecycleProcess
{

	private $source;
	private $target;

	private $db;
	private $prodDb;
	private $wsLink;

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
		$curProd = $this->getCurrentProduction();

		try {
			$prodDbConn = $this->db->connectTo($this->prodDb);
		} catch (DatabaseException $e) {
			$msg = "Unable to connect to production database";
			throw new RuntimeException($msg, 0, $e);
		}

		$logger->info("Deploying site from $this->source to $exportTarget");
		$this->verifyParameters($logger, $exportTarget);

		$queue = new ProcessQueue();
		$queue->add(new ExportProcess(
			$this->source,
			$exportTarget,
			LifecycleProcess::STAGED_TAG
		));
		$queue->add(new CompileProcess($exportTarget, Env::PROD));
		if ($curProd !== null) {
			$queue->add(new CopyUserContentProcess($curProd, $exportTarget));
		}
		$queue->add(new AlterDatabaseProcess($prodDbConn, $exportTarget));
		$queue->add(new CopySymlinksStep(
			$this->wsLink,
			"$exportTarget/target/htdocs"
		));
		$queue->add(new TagDeployedVersionStep($this->source, "v{$ts}"));
		$queue->add(new UpdateWebServerLinkProcess($this->wsLink, $exportTarget));
		$queue->add(new SymlinkUpdateStep("$this->target/current", $exportTarget));

		$result = $queue->execute($logger);
		if (!$result) {
			// TODO Should the target directory be cleaned up if it was created?
		}

		return $result;
	}

	public function setDatabaseConnection(DatabaseConnection $db) {
		$this->db = $db;
	}

	public function setProductionDatabase($productionDatabase) {
		$this->prodDb = $productionDatabase;
	}

	public function setWebServerLink($wsLink) {
		$this->wsLink = $wsLink;
	}


	/*
	 * Derive current production root from wsLink. If wsLink has been managed by
	 * the conductor set of deploy scripts it will be a link to
	 * <cur-prod>/target/htdocs
	 */
	private function getCurrentProduction() {
		if (!$this->wsLink) {
			return null;
		}

		$wsReal = realpath($this->wsLink);
		if (
			basename($wsReal) !== 'htdocs' ||
			basename(dirname($wsReal)) !== 'target'
		) {
			$logger->warning("Provided webserver link $this->wsLink is not a "
				. "Conductor managed symlink. Should point to the target/htdocs "
				. "Subdirectory of the current production site.");
			return null;
		}

		return dirname(dirname($wsReal));
	}

	private function verifyParameters($logger, $exportTarget) {
		if (file_exists($exportTarget)) {
			// Make sure target doesn't already exist
			// TODO Should the target be overwritten should the user be prompted for
			//      this decision?
			throw new InvalidArgumentException("Specified target directory already "
				. "exists: $exportTarget");
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
					. "deployed site from:\n\n  $this->wsLink\n"
				);
			}
		}

	}
}
