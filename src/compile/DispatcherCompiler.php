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
namespace zpt\cdt\compile;

/**
 * This class compiles the request dispatcher by placing the startup script in
 * the /htdocs directory as well as the .htaccess file that redirects all
 * requests to that script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DispatcherCompiler implements Compiler {

	public function compile($pathInfo, $ns, $env = 'dev') {
		copy(
			"$pathInfo[root]/vendor/zeptech/conductor/htdocs/.htaccess",
			"$pathInfo[target]/htdocs/.htaccess");
		copy(
			"$pathInfo[root]/vendor/zeptech/conductor/htdocs/srvr.php",
			"$pathInfo[target]/htdocs/srvr.php");
	}
}
