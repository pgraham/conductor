<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\cdt\compile;

use zpt\cdt\config\SiteConfiguration;
use ArrayObject;
use Exception;

/**
 * This class compiles a Configurator implementation for initiating the
 * conductor environment.
 *
 * In dev mode, this happens for every single request so this needs to happen
 * before almost anything else so that configuration complete for actual request
 * parsing, as well as for the other compilation steps.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigurationCompiler {

	/**
	 * Compile the site Configurator implementation for the site located at the
	 * given root path.
	 *
	 * @param string $root
	 *   The root path of site.
	 * @param string $env
	 *   The environment for which configuration is being compiled.
	 */
	public function compile($root, $env) {
		$cfg = array();

		$config = new SiteConfiguration($root, $env);

		$generator = new ConfiguratorGenerator($config);
		$generator->generate('Configurator');
	}
}
