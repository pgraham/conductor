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
// TODO Provide error checking for this
require 'SplClassLoader.php';

// This file should be located at <site-root>/target/htdocs/srvr.php
$siteRoot = realpath(__DIR__ . '/../..');

$cdtPath = "$siteRoot/lib/conductor/src";
spl_autoload_register(function ($classname) use ($cdtPath) {
  if (substr($classname, 0, 10) !== 'conductor\\') {
    return;
  }

  $relPath = str_replace('\\', '/', substr($classname, 10));
  $fullPath = "$cdtPath/$relPath.php";

  if (file_exists($fullPath)) {
    require $fullPath;
  }
});
\conductor\Conductor::init("$siteRoot/conductor.cfg.xml");
