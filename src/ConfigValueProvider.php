<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt;

use zpt\orm\Criteria;
use zpt\orm\Repository;

/**
 * This class retrieves configuration values from the database.
 *
 * These values differ from configuration in conductor.yml in that they are
 * visible to, and possibly modifiable by, the user.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigValueProvider
{

	private $persister;

	public function __construct(Repository $repository) {
		$this->persister = $repository->getPersister('zpt\cdt\model\ConfigValue');
	}

	/**
	 * Retrieve the configuration value with the given name.	In order for this to
	 * work the database must be setup to handle configuration values.
	 *
	 * @param string $name The name of the configuration value to retrieve.
	 */
	public function getConfigValue($name) {

		$c = new Criteria();
		$c->addEquals('name', $name);

		$rows = $this->persister->retrieve($c);
		if (count($rows) === 0) {
			return null;
		}

		$obj = $rows[0];
		return $obj->getValue();
	}
}
