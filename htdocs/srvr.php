<?php
/**
 * Server dispatcher.  Initiates php-resource-manager (conductor) and processes
 * the request.
 *
 * This file needs to be symlinked into the document root along with the
 * .htaccess file in the same directory as this file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */

// SplClassLoader needs to be available in the PHP include path
require 'SplClassLoader.php';

// This file should be located at <site-root>/target/htdocs/srvr.php
$siteRoot = realpath(__DIR__ . '/../..');

// Register a loader for conductor classes that follow the SPR-0 compliant
// package structure
$cdtLdr = new SplClassLoader('zpt\cdt', "$siteRoot/lib/conductor");
$cdtLdr->register();

// Initiate the environment
\zpt\cdt\Conductor::init($siteRoot);
