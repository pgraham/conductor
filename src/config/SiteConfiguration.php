<?php
/**
 * =============================================================================
 * Copyright (c) 2014, Philip Graham
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
namespace zpt\cdt\config;

use ArrayObject;
use Exception;

/**
 * This class encapsulates a sites configurable environment values.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SiteConfiguration
{

	private $root;
	private $env;

	// Composite configuration
	private $pathInfo;
	private $namespace;
	private $dbConfig;

	// Path configuration
	private $logDir;
	private $target;

	/**
	 * Load the configuration stored in the specified file.
	 */
	public function __construct($root, $path, $env) {
		$this->root = $root;
		$this->env = $env;

		$xmlCfg = simplexml_load_file("$root/$path", 'SimpleXMLElement',
			LIBXML_NOCDATA);

		if (!isset($xmlCfg->namespace)) {
			throw new ConfigurationException("The site's namespace is not set.");
		}

		$this->pathInfo = $this->parsePathInfo($root, $xmlCfg);
		$this->namespace = (string) $xmlCfg->namespace;
		$this->dbConfig = new DatabaseConfiguration($xmlCfg);

		$logDir = '';
		if (isset($this->xmlCfg->logDir)) {
			$logDir = (string) $this->xmlCfg->logDir;
		}
		$this->logDir = $logDir;
	}

	public function getDbConfig() {
		return $this->dbConfig;
	}

	public function getEnv() {
		return $this->env;
	}

	public function getLogDir() {
		return $this->logDir;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function getPathInfo() {
		return $this->pathInfo;
	}

	public function getTarget() {
		return $this->target;
	}

	private function parsePathInfo($root, $xmlCfg) {
		$webRoot = isset($xmlCfg->webRoot)
			? (string) $xmlCfg->webRoot
			: '/';

		$docRoot = "$root/htdocs";
		$cdtRoot = "$root/vendor/zeptech/conductor";
		$src = "$root/src";
		$target = "$root/target";

		$this->target = $target;

		return new ArrayObject(array(
			'root' => $root,
			'webRoot' => $webRoot,
			'docRoot' => $docRoot,
			'cdtRoot' => $cdtRoot,
			'src' => $src,
			'target' => $target
		));
	}

}
