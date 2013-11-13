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

		private static $composerPath;
		private static $composerLoader;
		private static $siteRootDir;

		public static function getComposerPath($baseDir = null) {
			if (self::$composerPath === null) {
				if ($baseDir === null) {
					$baseDir = __DIR__;
				}
				foreach (self::$autoloadFiles as $file) {
					$path = "$baseDir/$file";
					if (file_exists($path)) {
						self::$composerPath = dirname(realpath($path));
						break;
					}
				}

				if (self::$composerPath === null) {
					echo "Unable to find composer!\n";
					exit(1);
				}
			}
			return self::$composerPath;
		}

		public static function getComposerLoader($baseDir = null) {
			if (self::$composerLoader === null) {
				$composerPath = getComposerPath($baseDir);
				$loader = include "$composerPath/autoload.php";
				self::$composerLoader = $loader;

				\FunBox::init();
			}
			return self::$composerLoader;
		}

		public static function getSiteRootDir($baseDir = null) {
			if (self::$siteRootDir === null) {
				$composerPath = self::getComposerPath($baseDir);
				$devDir = dirname($composerPath);
				self::$siteRootDir = $devDir;
			}
			return self::$siteRootDir;
		}
	}
}

/*
 * =============================================================================
 * Expose BinCommon functions in the global namespace.
 * =============================================================================
 */
namespace {

	use zpt\cdt\bin\BinCommon;

	/**
	 * This function finds and returns the Composer vendor directory relative to
	 * the script.
	 *
	 * @return Path to the composer vendor directory or `false` if not found.
	 */
	function getComposerPath($baseDir = null) {
		return BinCommon::getComposerPath($baseDir);
	}

	/**
	 * Include the composer loader and return it. Abort script execution if not
	 * found.
	 *
	 * @return Composer loader instance.
	 */
	function getComposerLoader() {
		return BinCommon::getComposerLoader();
	}

	/**
	 * Find and return the absolute path to the root of the site.
	 *
	 * @return string The root path of the site.
	 */
	function getSiteRootDir() {
		return BinCommon::getSiteRootDir();
	}

	/**
	 * Log a header
	 */
	function binLogHeader($msg) {
		echo "\n \033[1m« $msg »\033[0m\n";
	}

	/**
	 * Log an error message.
	 */
	function binLogError($msg, $depth = 0) {
		$tabbing = str_repeat(' ', $depth);
		echo "$tabbing \033[31m✖\033[0m $msg\n";
	}

	/**
	 * Log an info message.
	 */
	function binLogInfo($msg, $depth = 0) {
		$tabbing = str_repeat(' ', $depth);
		echo "$tabbing \033[39m➜\033[0m $msg\n";
	}

	/**
	 * Log a success message.
	 */
	function binLogSuccess($msg, $depth = 0) {
		$tabbing = str_repeat(' ', $depth);
		echo "$tabbing \e[32m✔\e[0m $msg\n";
	}
}
