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
 * This class parses the cdt-init steps to execute from a given set of command
 * line arguments.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInitStepsParser {

	private static $steps = [ CdtInit::STEP_FS, CdtInit::STEP_DB ];

	public function parse($args) {
		// By default, perform all steps with default options
		if (count($args) === 0) {
			return array_combine(
				self::$steps,
				array_fill(0, count(self::$steps), [])
			);
		}

		$steps = [];
		$curStep = null;
		foreach ($args as $arg) {
			// TODO - WHAT!? This needs to be tested
			if (in_array($arg, $steps)) {
				$steps[$arg] = [];
				$curStep = $arg;
			} else if ($curStep !== null) {
					$steps[$curStep][] = $arg;
			}
		}

		if (count($steps) === 0) {
			// TODO Warn user that no valid steps were found so nothing will be done
		}

		return $steps;
	}
}
