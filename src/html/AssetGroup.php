<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\html;

use zpt\cdt\compile\resource\ResourceDiscoverer;
use zpt\oobo\Head;

/**
 * This class encapsulates an asset group. An asset group is a collection of
 * asset files that are combined during compilation into a single asset.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AssetGroup extends Asset {

	private $assets = [];

	public function __construct($docRoot, $path) {
		parent::__construct($docRoot, $path);

		error_log("Creating Asset group for $path : $docRoot");
		$grpParts = explode('/', $this->path);
		$grpType = array_shift($grpParts);
		$grpPath = dirname($path);
		$grpName = basename($path);

		$resourceDiscoverer = new ResourceDiscoverer("$docRoot/$grpPath", $grpType);
		$files = $resourceDiscoverer->discover($grpName);

		foreach ($files as $file) {
			$this->assets[] = new Asset($docRoot, "$grpPath/$file");
		}
	}

	public function addToHead(Head $head) {
		foreach ($this->assets as $asset) {
			$asset->addToHead($head);
		}
	}
}
