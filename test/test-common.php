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

// Register a class loader for conductor classes that follow the legacy package
// structure -- This will eventually be eliminated
$cdtPath = realpath(__DIR__ . '/..');
spl_autoload_register(function ($classname) use ($cdtPath) {
  if (substr($classname, 0, 10) !== 'conductor\\') {
    return;
  }

  $relPath = str_replace('\\', '/', substr($classname, 10));
  $fullPath = "$cdtPath/src/$relPath.php";

  if (file_exists($fullPath)) {
    require $fullPath;
  }
});

// Register a loader for conductor classes that follow the SPR-0 compliant
// package structure
$cdtLdr = new SplClassLoader('zpt\cdt', $cdtPath);
$cdtLdr->register();
