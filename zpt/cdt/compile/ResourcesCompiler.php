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

use \zpt\cdt\compile\resource\ResourceCompiler;
use \DirectoryIterator;

/**
 * This class oversees the compilation of all necessary resources for a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourcesCompiler implements Compiler {

	private $resourceCompiler;
	private $resourceGroups;

	public function __construct() {
		$this->resourceCompiler = new ResourceCompiler();

		$this->resourceGroups = array();
		$this->addResourceGroup('css', 'cdt.core');
		$this->addResourceGroup('css', 'cdt.widget');
		$this->addResourceGroup('css', 'cdt.cmp');
	}

	public function addResourceGroup($type, $group) {
		if (!isset($this->resourceGroups[$type])) {
			$this->resourceGroups[$type] = array( $group );
			return;
		}

		if (!in_array($group, $this->resourceGroups[$type])) {
			$this->resourceGroups[$type][] = $group;
		}
	}

	public function compile($pathInfo, $ns, $env = 'dev') {
		$resourceOut = "$pathInfo[target]/htdocs";

		// -------------------------------------------------------------------------
		// Phase 1
		// -------
		// Copy, resolve and minify javascript css and img resources.
		// -------------------------------------------------------------------------

		// Compile conductor resources
		// ---------------------------
		$resourceSrc = "$pathInfo[cdtRoot]/htdocs";

		// Compile base javascript
		$this->resourceCompiler->compile(
			"$pathInfo[cdtRoot]/resources/base.tmpl.js",
			"$resourceOut/js/base.js",
			array(
				'rootPath' => $pathInfo['webRoot'],
				'jsns' => $ns
			));

		// -------------------------------------------------------------------------
		// ORDER HERE IS SIGNIFICANT
		// -------------------------
		// All resources of the same type (js, css, img) are compiled into the
		// same so location.	The conductor resources are compiled first followed by
		// the modules resources with the site resources compiled last.
		// This allows modules to override conductor resources and the site's
		// resources to override both conductor and module resources.
		//
		// By convention, all conductor resources are placed within a directory
		// named cdt.  Modules place their resources in a directory specific for 
		// that modules and sites place resource in a directory named the same as 
		// the site nickname.
		//
		// WARNING: When multiple modules declare the same file the result is
		//          non-deterministic
		// -------------------------------------------------------------------------
		$this->resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
		$this->resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
		$this->resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");

		$modulesPath = "$pathInfo[root]/modules";
		if (file_exists($modulesPath)) {
			$modules = new DirectoryIterator($modulesPath);
			foreach ($modules as $module) {
				if ($module->isDot() || !$module->isDir()) {
					continue;
				}

				$resourceSrc = "{$module->getPathname()}/htdocs";
				$this->resourceCompile("$resourceSrc/js", "$resourceOut/js");
				$this->resourceCompile("$resourceSrc/css", "$resourceOut/css");
				$this->resourceCompile("$resourceSrc/img", "$resourceOut/img");
			}
		}

		// Compile site resources
		// ----------------------
		$resourceSrc = "$pathInfo[src]/resources";
		$this->resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
		$this->resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
		$this->resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");
	}

	public function combineResourceGroups($resources) {
		// -------------------------------------------------------------------------
		// Phase 2: Grouping
		// -----------------
		// In non-dev mode, the target directory resources will be combined into 
		// larger resources based on grouping rules. For now only specific resource
		// groups are combined but over time rules may emerge that allow this to be
		// done universally.
		// -------------------------------------------------------------------------

		foreach ($this->resourceGroups as $type => $groups) {
			foreach ($groups as $group) {
				$this->resourceCompiler->combineGroup($resources, $type, $group);
			}
		}
	}
}
