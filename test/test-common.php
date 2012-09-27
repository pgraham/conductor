<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * This file sets up the environment for running tests.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package conductor/test
 */

require 'SplClassLoader.php';

// Initialize Mockery
// -----------------------------------------------------------------------------
require 'Mockery/Loader.php';
require 'Hamcrest/Hamcrest.php';
$loader = new \Mockery\Loader();
$loader->register();

// Register a loaders for conductor classes and dependencies that follow a SPR-0
// compliant package structure
// -----------------------------------------------------------------------------
$cdtPath = realpath(__DIR__ . '/..');

$cdtLdr = new SplClassLoader('zpt\cdt', $cdtPath);
$cdtLdr->register();

$reedLdr = new SplClassLoader('zpt\util', "$cdtPath/lib/reed");
$reedLdr->register();

// Register loaders for dependencies
// -----------------------------------------------------------------------------

