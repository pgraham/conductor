<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */

/**
 * Perform common setup for bin/ scripts.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
require_once __DIR__ . '/common.php';

$loader = getComposerLoader();
$devDir = getSiteRootDir();

// Make sure conductor configuration file exists
if (!file_exists("$devDir/conductor.yml")) {
  echo "Unable to locate conductor configuration file in $devDir\n";
  exit(1);
}

// The site's nickname is the name of the dev dir.  This will be used to
// generate defaults for missing options
$siteNick = basename($devDir);
