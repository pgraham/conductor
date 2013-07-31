<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\bin {

	/**
	 * This class encapsulates functionality common to the bin scripts.
	 *
	 * @author Philip Graham <philip@zeptech.ca>
	 */
	class BinCommon {

		private static $autoloadFiles = array(
			'../vendor/autoload.php',
			'../../../autoload.php'
		);

		public static function getComposerPath($baseDir = null) {
			if ($baseDir === null) {
				$baseDir = __DIR__;
			}
			foreach (self::$autoloadFiles as $file) {
				$path = "$baseDir/$file";
				if (file_exists($path)) {
					return dirname(realpath($path));
				}
			}
			return false;
		}

	}
}

/*
 * =============================================================================
 * Expose BinCommon functions in the global namespace.
 * =============================================================================
 */
namespace {

	/**
	 * This function finds the Composer vendor directory relative to the script
	 * and includes the Composer autoloader.
	 *
	 * @return Path to the composer vendor directory or `false` if not found.
	 */
	function composerInit($baseDir = null) {
		return zpt\cdt\bin\BinCommon::composerInit($baseDir);
	}

}
