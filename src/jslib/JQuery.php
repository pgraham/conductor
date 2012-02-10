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

use \conductor\Conductor;
use \oboe\Element;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates the files required for load the jquery library.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JQuery implements Library {

  public function __construct(array $opts = null) {}

  public function compile(WebSitePathInfo $pathInfo) {}

  public function inc(WebSitePathInfo $pathInfo, $devMode) {
    $jQueryName = 'jquery.min.js';
    if ($devMode) {
      $jQueryName = 'jquery.js';
    }
    $jqPath = 'http://ajax.googleapis.com/ajax/libs/jquery/' .
      Conductor::JQUERY_VERSION . "/$jQueryName";

    Element::js($jqPath)->addToHead();
  }

  public function link(WebSitePathInfo $pathInfo, $devMode) {}

}
