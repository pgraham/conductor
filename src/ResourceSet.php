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
namespace conductor;

/**
 * This class represents a list of related resources.  All resources in a
 * resource list must be relative to a specified base path.  The base can be
 * '/'.  The exception to this are external resources which reside on another
 * server.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceSet {

  private $_srcPath;
  private $_external = array();
  private $_scripts = array();
  private $_sheets = array();
  private $_images = array();

  public function __construct($srcPath = '/') {
    $this->_srcPath = $srcPath;
  }

  public function getSrcPath() {
    return $this->_srcPath;
  }

  public function getExternal() {
    return $this->_external;
  }

  public function getImages() {
    return $this->_images;
  }

  public function getScripts() {
    return $this->_scripts;
  }

  public function getSheets() {
    return $this->_sheets;
  }

  public function setExternal(array $external) {
    $this->_external = $external;
  }

  public function setImages(array $images) {
    $this->_images = $images;
  }

  public function setScripts(array $scripts) {
    $this->_scripts = $scripts;
  }

  public function setSheets(array $sheets) {
    $this->_sheets = $sheets;
  }
}
