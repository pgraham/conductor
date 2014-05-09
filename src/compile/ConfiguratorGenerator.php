<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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

use zpt\cdt\config\SiteConfiguration;
use zpt\opal\CompanionGenerator;
use ArrayObject;
use Exception;

/**
 * This class generates a Configurator class that is used in production mode to
 * avoid having to parse the XML config file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfiguratorGenerator extends CompanionGenerator {

	private $cfg;

	public function __construct(SiteConfiguration $cfg) {
		parent::__construct($cfg->getPathInfo()['target']);

		$this->cfg = $cfg;
	}

	public function getCompanionNamespace($defClass) {
		return 'zpt\dyn';
	}

	public function getTemplatePath($defClass) {
		return __DIR__ . '/Configurator.tmpl.php';
	}

	public function getValues($className) {
		$pathInfo = $this->cfg->getPathInfo();
		$namespace = $this->cfg->getNamespace();
		$dbConfig = $this->cfg->getDbConfig();

		return array(
			'pathInfo' => $pathInfo->asArray(),
			'docRootLen' => strlen($pathInfo['htdocs']),
			'webRootLen' => strlen($pathInfo['webRoot']),
			'namespace' => $namespace,
			'dbConfig' => $dbConfig->asArray(),
			'env' => $this->cfg->getEnv(),
			'logDir' => $this->cfg->getLogDir()
		);
	}

}
