<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\compile;

use DirectoryIterator;
use Iterator;

/**
 * Iterator for the site's modules.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModuleIterator implements Iterator
{

	private $modules = [];

	public function __construct($moduleDir) {
		$dir = new DirectoryIterator($moduleDir);
		foreach ($dir as $f) {
			if (!$f->isDot() && $f->isDir()) {
				$this->modules[] = $f->getPathname();
			}
		}
	}

	public function rewind() {
		return reset($this->modules);
	}

	public function current() {
		return current($this->modules);
	}

	public function valid() {
		return current($this->modules) !== false;
	}

	public function key() {
		return key($this->modules);
	}

	public function next() {
		return next($this->modules);
	}
}
