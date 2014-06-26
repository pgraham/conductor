<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\html;

use zpt\oobo\Element;
use zpt\oobo\Head;

/**
 * This class encapsulates a static resource.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Asset {

	protected $path;
	protected $type;

	/**
	 * Create an Asset for the given web path. A {@link:glossary web path} is
	 * relative to the {@link:glossary document root}.
	 *
	 * @param string $docRoot
	 *   The site's {@link:glossary document root}
	 * @param string $path
	 *   Relative path to the asset from the document root.
	 */
	public function __construct($docRoot, $path) {
		$this->path = String($path)->ltrim('/');

		if ($this->path->endsWith('.css')) {
			$this->type = 'css';
		} else {
			$this->type = 'js';
		}
	}

	public function addToHead(Head $head) {
		if ($this->type === 'css') {
			$head->add(Element::css(_P("/$this->path")));
		} else {
			$head->add(Element::js(_P("/$this->path")));
		}
	}
}
