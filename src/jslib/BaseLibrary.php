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

use \conductor\Resource;
use \oboe\Element;
use \reed\File;
use \reed\WebSitePathInfo;

/**
 * This class implements basic functionality for most javascript libraries.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class BaseLibrary implements Library {

  protected $opts;

  private $_libDir;

  public function compile(WebSitePathInfo $pathInfo) {
    // TODO minimize js/css
  }

  public function inc(WebSitePathInfo $pathInfo, $devMode) {
    $webOut = $pathInfo->fsToWeb($this->_getOutPath($pathInfo));

    $files = $this->getIncluded($pathInfo, $devMode);
    foreach ($files AS $file) {
      $type = Resource::getResourceType($file);
      if (substr($file, 0, 1) === '/') {
        // Path is defined as relative to the web root, not the library output
        // directory
        $webPath = $pathInfo->webPath($file);
      } else {
        // Path is defined as reletive to the library output directory
        $webPath = "$webOut/$file";
      }

      switch ($type) {
        case 'js':
        Element::js($webPath)->addToHead();
        break;

        case 'css':
        Element::css($webPath)->addToHead();
        break;

        default:
        assert("false /* Unrecognized inclusion type: $type */");
      }
    }
  }

  public function link(WebSitePathInfo $pathInfo, $devMode) {
    $srcPath = $this->_getSrcPath($pathInfo);
    $outPath = $this->_getOutPath($pathInfo);
    if (!file_exists($outPath)) {
      mkdir($outPath, 0755, true);
    }

    $files = $this->getLinked($pathInfo, $devMode);
    foreach ($files AS $file) {
      if (is_array($file)) {
        if (substr($file['src'], 0, 1) === '/') {
          $fileSrc = $file['src'];
        } else {
          $fileSrc = File::joinPaths($srcPath, $file['src']);
        }

        $fileOut = File::joinPaths($outPath, $file['out']);
      } else {
        $fileSrc = File::joinPaths($srcPath, $file);
        $fileOut = File::joinPaths($outPath, $file);
      }

      $fileOutDir = dirname($fileOut);
      if (!file_exists($fileOutDir)) {
        mkdir($fileOutDir, 0755, true);
      }

      copy($fileSrc, $fileOut);
    }
  }

  /** Must be called by the implementing class' constructor. */
  protected function init($libDir, array $opts = null) {
    if ($opts === null) {
      $opts = array();
    }
    $this->opts = $opts;

    $this->_libDir = $libDir;
  }

  /**
   * Implementations must provide a list of files which are linked into the
   * document root.  Each file is defined as an array with two elements, 'src',
   * which is where the file lives relative to the library's root directory, and
   * 'out', which is where the file is to be output relative to the library's
   * output directory within the document root.
   *
   * Examples of how elements are mapped to source and output paths.
   *
   *   'stylesheet.css':
   *     src: <lib-root>/stylesheet.css
   *     out: <lib-out>/stylesheet.css
   *
   *   array( 'src' => 'stylesheet.css', 'out' => 'css/stylesheet.css' ):
   *     src: <lib-root>/stylesheet.css
   *     out: <lib-out>/css/stylesheet.css
   *
   *   array( 'src' => '/absolute/path/to/file/stylesheet.css', 'out' => 'stylesheet.css'):
   *     src: /absolute/path/to/file/stylesheet.css
   *     out: <lib-out>/stylesheet.css
   *
   * @param boolean $devMode
   * @return array
   */
  protected abstract function getLinked($pathInfo, $devMode);

  protected abstract function getIncluded($pathInfo, $devMode);

  /* Get the path to the library's source files. */
  private function _getSrcPath(WebSitePathInfo $pathInfo) {
    return File::joinPaths($pathInfo->getLibPath(), 'jslib', $this->_libDir);
  }

  /* Get the path to where the library's files are made web-accessible. */
  private function _getOutPath(WebSitePathInfo $pathInfo) {
    return File::joinPaths($pathInfo->getWebTarget(), $this->_libDir);
  }
}
