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
namespace conductor\resources;

use \conductor\compile\Compilable;
use \conductor\Resource;
use \reed\WebSitePathInfo;

/**
 * This class generates a site specific version of conductor.js
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConductorJs implements Compilable {

  private $_js;

  public function __construct() {
    $this->_js = new Resource('conductor.js');
  }

  public function compile(WebSitePathInfo $pathInfo) {
    // TODO Minimize js
  }

  public function inc(WebSitePathInfo $pathInfo, $devMode) {
    $this->_js->addToPage();
  }

  public function link(WebSitePathInfo $pathInfo, $devMode) {
    $workingPath = Resource::getResourcePath('working.gif');
    $workingImgInfo = getimagesize($workingPath);
    $values = array(
      'rootPath'    => $pathInfo->getWebRoot(),
      'targetPath'  => $pathInfo->fsToWeb($pathInfo->getWebTarget()),
      'imgWidth'    => $workingImgInfo[0],
      'imgHeight'   => $workingImgInfo[1]
    );

    $this->_js->compile($values);
  }
}
