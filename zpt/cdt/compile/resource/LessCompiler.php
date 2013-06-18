<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile\resource;

use \CSSmin;
use \lessc;

/**
 * This class compiles a given less file into a css file at the given output
 * location.
 */
class LessCompiler {
	
	private $cssMin;
	private $lessc;

	public function __construct() {
		$this->cssMin = new CSSmin(false /* Don't change php mem settings */);
		$this->lessc = new lessc();
	}

	public function compile($src, $dest) {
		$less = file_get_contents($src);
		$css = $this->lessc->compile($less);
		$minified = $this->cssMin->run($css);
		file_put_contents($dest, $minified);
	}
}
