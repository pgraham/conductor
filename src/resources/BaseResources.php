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

use \conductor\Conductor;

/**
 * This class encapsulates a the base set of resources for the platform.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class BaseResources {

  private $_cdtJs;
  private $_toCompile;
  private $_toInclude;

  public function __construct() {
    $img = array(
      new Resource('working.gif');
    );

    $css = array(
      new Resource('reset.css'),
      new Resource('cdt.css'),
      new Resource('login.css');
    );

    $js = array(
      new Resource('utility.js'),
      new Resource('jquery-dom.js'),
      new Resource('login.js')
    );

    $this->_cdtJs = new Resource('conductor.js');
    $this->_toCompile = array_merge($img, $css, $js);
    $this->_toInclude = array_merge($css, $js);
  }

  public function compile() {
    $pathInfo = Conductor::getPathInfo();
    $workingPath = Resource::getResourcePath('working.gif');
    $workingImgInfo = getimagesize($workingPath);
    $values = array(
      'rootPath'    => $pathInfo->getWebRoot(),
      'targetPath'  => $pathInfo->fsToWeb($pathInfo->getWebTarget()),
      'imgWidth'    => $workingImgInfo[0],
      'imgHeight'   => $workingImgInfo[1]
    );
    $this->_cdtJs->compile($values);
    
    foreach ($this->_toCompile AS $r) {
      $r->compile();
    }
  }

  public function inc() {
    $this->_cdtJs->addToPage();
    foreach ($this->_toInclude AS $r) {
      $r->addToPage();
    }
  }
}
