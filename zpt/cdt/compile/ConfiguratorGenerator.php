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

use \zpt\pct\AbstractGenerator;
use \ArrayObject;
use \Exception;

/**
 * This class generates a Configurator class that is used in production mode to
 * avoid having to parse the XML config file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfiguratorGenerator extends AbstractGenerator {

	public static $actorNamespace = 'zpt\dyn';

	private $root;
	private $env;
	private $xmlCfg;

	public function __construct($root, $env, $xmlCfg) {
		parent::__construct("$root/target");

		$this->root = $root;
		$this->env = $env;
		$this->xmlCfg = $xmlCfg;
	}

	public function getTemplatePath() {
		return __DIR__ . '/Configurator.tmpl.php';
	}

	public function getValues($className) {
		$pathInfo = $this->parsePathInfo($this->root, $this->xmlCfg);
		$namespace = $this->parseNamespace($this->xmlCfg);
		$dbConfig = $this->parseDbConfig($this->xmlCfg);

		$logDir = '';
		if (isset($this->xmlCfg->logDir)) {
			$logDir = (string) $this->xmlCfg->logDir;
		}

		return array(
			'pathInfo' => $pathInfo->getArrayCopy(),
			'docRootLen' => strlen($pathInfo['docRoot']),
			'webRootLen' => strlen($pathInfo['webRoot']),
			'namespace' => $namespace,
			'dbConfig' => $dbConfig,
			'env' => $this->env,
			'logDir' => $logDir
		);
	}

	private function parseDbConfig($xmlCfg) {
		if (!isset($xmlCfg->db)) {
			throw new Exception('No database configuration found');
		}
		$dbConfig = array();

		if (!isset($xmlCfg->db->username)) {
			throw new Exception('No database username specified');
		}
		$dbConfig['db_user'] = (string) $xmlCfg->db->username;

		if (!isset($xmlCfg->db->password)) {
			throw new Exception('No database password specified');
		}
		$dbConfig['db_pass'] = (string) $xmlCfg->db->password;

		if (!isset($xmlCfg->db->schema)) {
			throw new Exception('No database schema specified');
		}
		$dbConfig['db_schema'] = (string) $xmlCfg->db->schema;

		$dbConfig['db_driver'] = (isset($xmlCfg->db->driver))
			? (string) $xmlCfg->db->driver
			: 'mysql';

		$dbConfig['db_host'] = (isset($xmlCfg->db->host))
			? (string) $xmlCfg->db->host
			: 'localhost';

		return $dbConfig;
	}

	private function parseNamespace($xmlCfg) {
		if (!isset($xmlCfg->namespace)) {
			throw new Exception("The site's namespace is not configured");
		}
		return (string) $xmlCfg->namespace;
	}

	private function parsePathInfo($root, $xmlCfg) {
		$webRoot = isset($xmlCfg->webRoot)
			? (string) $xmlCfg->webRoot
			: '/';

		$docRoot = "$root/htdocs";
		$lib = "$root/lib";
		$src = "$root/src";
		$target = "$root/target";

		return new ArrayObject(array(
			'root' => $root,
			'webRoot' => $webRoot,
			'docRoot' => $docRoot,
			'lib' => $lib,
			'src' => $src,
			'target' => $target
		));
	}

}
