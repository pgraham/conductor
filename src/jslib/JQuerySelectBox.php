<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
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
namespace conductor\jslib;

/**
 * This class encapuslates the files required for jquery-selectBox.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JQuerySelectBox extends BaseLibrary {

  public function __construct(array $opts = null) {
    $this->init(JsLib::JQUERY_SELECTBOX, $opts);
  }

  protected function getLinked($pathInfo, $devMode) {
    return array(
      'jquery.selectBox.min.js',
      'jquery.selectBox.css',
      'jquery.selectBox-arrow.gif'
    );
  }

  protected function getIncluded($pathInfo, $devMode) {
    return array(
      'jquery.selectBox.min.js',
      'jquery.selectBox.css'
    );
  }
}
