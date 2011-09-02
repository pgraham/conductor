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

  /* The base path for all source files */
  private $_srcPath;

  /* The web-accessible path where resources will be made available */
  private $_webPath;

  /* Any dependencies which reside on other servers */
  private $_external = array();

  private $_scripts = array();
  private $_sheets = array();
  private $_images = array();

  /**
   * Create a new resource set for resource which live under the given source
   * path and will be made available under the given web-accessible path.
   *
   * @param string $srcPath The path under which all source files are found.
   *   If the given path is relative it will be treated as relative to the
   *   default.  Default: The website's base source path.
   * @param string $webPath The web-accessible path under which all files will
   *   be made available. If the path is relative it will be treated as relative
   *   to the default.  Default: The website's web-accessible target path.
   */
  public function __construct($srcPath = null, $webPath = null) {
    $pathInfo = Conductor::getPathInfo();

    if ($srcPath === null) {
      $srcPath = $pathInfo->getSrcPath();
    }  else if (substr($srcPath, 0, 1) !== '/') {
      $srcPath = $pathInfo->getSrcPath() . "/$srcPath";
    }
    $this->_srcPath = $srcPath;

    if ($webPath === null) {
      $webPath = $pathInfo->getWebTarget();
    } else if (substr($webPath, 0, 1) !== '/') {
      $webPath = $pathInfo->getWebTarget() . "/$webPath";
    }
    $this->_webPath = $webPath;
  }

  public function getSrcPath() {
    return $this->_srcPath;
  }

  public function getWebPath() {
    return $this->_webPath;
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
