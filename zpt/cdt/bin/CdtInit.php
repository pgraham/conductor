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
namespace zpt\cdt\bin;

/**
 * This class encapsulates logic for the cdt-init script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInit {

	const STEP_FS = 'fs';
	const STEP_DB = 'db';

	private $baseDir;
	private $ns;

	public function __construct($baseDir, $ns) {
		$this->baseDir = $baseDir;
		$this->ns = $ns;
	}

	public function execute($args) {
		$steps = (new CdtInitStepsParser)->parse($args);

		foreach ($steps as $step => $opts) {
			switch ($step) {
				case self::STEP_FS:
				(new CdtInitFsStep)->execute($this->baseDir, $this->ns, $opts);
				break;

				case self::STEP_DB:
				(new CdtInitDbStep)->execute($this->baseDir, $this->ns, $opts);
				break;
			}
		}
	}
}
