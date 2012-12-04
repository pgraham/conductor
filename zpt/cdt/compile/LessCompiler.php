<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile;

use \lessc;

/**
 * This class compiles a given less file into a css file at the given output
 * location.
 */
class LessCompiler {
  
  private $_lessc;

  public function __construct() {
    $this->_lessc = new lessc();
  }

  public function compile($src, $dest) {
    file_put_contents($dest, $this->_lessc->compile(file_get_contents($src)));
  }
}
