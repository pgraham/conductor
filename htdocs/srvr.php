<?php
/**
 * =============================================================================
 * Copyright (c) 2012, Philip Graham
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

/**
 * Server dispatcher.  Initiates php-resource-manager (conductor) and processes
 * the request.
 *
 * This file needs to be symlinked into the document root along with the
 * .htaccess file in the same directory as this file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */

// This file should be located at <site-root>/target/htdocs/srvr.php
$siteRoot = realpath(__DIR__ . '/../..');

$loader = require_once "$siteRoot/vendor/autoload.php";

// Process the request
\zpt\cdt\Conductor::init($siteRoot, $loader);
