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
 * This class is used to initialize the filesystem of a Conductor site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CdtInitFsStep {

	private static $dirs = [
		'src' => [
			'resources' => [
				'i18n'
			],
			'htdocs' => [
				'css',
				'img',
				'js',
				'jslib'
			],
			'sql'
		],
		'modules',
		'target' => [
			'htdocs'
		]
	];

	private static $nsDirs = [
		'html',
		'model',
		'srvc'
	];

	public function execute($baseDir, $ns, $opts) {
		$dirs = self::$dirs;
		$dirs['src'][$ns] = self::$nsDirs;

		binLogHeader("Creating Conductor Directories");
		$this->mkDirs($baseDir, $dirs);
		binLogSuccess("Finished creating directories");

		// Seed the target
		binLogHeader("Seeding htdocs/");
		$this->seed($baseDir);
		binLogSuccess("Done.");

		// Change target/ group to www-data and enable group write
		binLogHeader("Setting Permissions");
		$this->setPermissions($baseDir);
		binLogSuccess("Done.");
	}

	private function mkDirs($baseDir, $dirs, $logStripPrefix = null) {
		if ($logStripPrefix === null) {
			$logStripPrefix = strlen($baseDir);
		}
		$dirSuffix = substr($baseDir, $logStripPrefix);

		foreach ($dirs as $key => $value) {
			if (is_array($value)) {
				binLogInfo("Creating $dirSuffix/$key");
				$this->mkDir("$baseDir/$key");
				$this->mkDirs("$baseDir/$key", $value, $logStripPrefix);
			} else {
				binLogInfo("Creating $dirSuffix/$value");
				$this->mkDir("$baseDir/$value");
			}
		}
	}

	private function mkDir($dir) {
		if (!file_exists($dir)) {
			mkdir($dir, 0755);
		} else {
			binLogWarning("Directory already exists", 1);
		}
	}

	private function seed($baseDir) {
		$cdt = "$baseDir/vendor/zeptech/conductor";
		copy("$cdt/htdocs/.htaccess", "$baseDir/target/htdocs/.htaccess");
		copy("$cdt/htdocs/srvr.php", "$baseDir/target/htdocs/srvr.php");
	}

	private function setPermissions($baseDir) {
		// TODO Make apache user configurable
		$webWritable = [
			"$baseDir/target",
			"$baseDir/target/htdocs",
			"$baseDir/target/htdocs/.htaccess",
			"$baseDir/target/htdocs/srvr.php"
		];
		foreach ($webWritable as $chPerms) {
			$success = chgrp($chPerms, 'www-data');
			if (!$success) {
				binLogError("Unable to change group of $chPerms, you will need to manually change the directory's group to www-data");
			}

			$perms = is_dir($chPerms) ? 0775 : 0644;
			$success = chmod($chPerms, $perms);
			if (!$success) {
				binLogError("Unable to change permissions of $chPerms, you will need to manually enable group write for the directory");
			}
		}
	}
}
