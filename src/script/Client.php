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
namespace conductor\script;

use \conductor\Resource;

use \reed\WebSitePathInfo;

/**
 * This class compiles the conductor client javascript.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Client {

  private $_resources = array();

  public function __construct(WebSitePathInfo $pathInfo) {
    $working = new Resource('working.gif', $pathInfo);
    $this->_resources[] = $working;

    $templateValues = null;
    if (defined('DEBUG') && DEBUG === true) {
      // Prepare template values for conductor.js resource
      $workingImgInfo = getimagesize($working->getFsPath());
      $templateValues = array(
        'basePath'  => $pathInfo->getWebAccessibleTarget(),
        'imgWidth'  => $workingImgInfo[0],
        'imgHeight' => $workingImgInfo[1]
      );
    }
    $this->_resources[] = new Resource('conductor.js', $pathInfo,
      $templateValues);
  }

  public function addToHead() {
    foreach ($this->_resources AS $resource) {
      $resource->addToHead();
    }
  }
}