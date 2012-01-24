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

use \conductor\Exception;
use \conductor\Library;
use \oboe\Element;
use \reed\File;

/**
 * This class encapsulates the file lists for different Galleria themes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Galleria implements Library {

  const LIBNAME = 'galleria';

  private static $_themeFiles;
  
  private function _initThemeFiles() {
    if (self::$_themeFiles !== null) {
      return;
    }

    $src = File::joinPaths('src', 'themes');
    $out = 'themes';

    $themeFiles = array(
      'classic' => array(
        'galleria.classic.js',
        'galleria.classic.css',
        'classic-loader.gif',
        'classic-map.png'
      ),
      'classic-light' => array(
        'galleria.classic-light.js',
        'galleria.classic-light.css',
        'classic-light-loader.gif',
        'classic-light-map.png'
      )
    );

    self::$_themeFiles = array();
    foreach ($themeFiles AS $name => $files) {
      self::$_themeFiles[$name] = array();

      $themeSrc = File::joinPaths($src, $name);
      $themeOut = File::joinPaths($out, $name);
      foreach ($files AS $file) {
        self::$_themeFiles[$name][] = array(
          'src' => File::joinPaths($themeSrc, $file),
          'out' => File::joinPaths($themeOut, $file)
        );
      }
    }
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_srcPath;
  private $_outPath;
  private $_webOut;

  public function __construct($srcPath, $outPath, $webOut) {
    self::_initThemeFiles();

    $this->_srcPath = $srcPath;
    $this->_outPath = $outPath;
    $this->_webOut = $webOut;
  }

  public function link(array $opts = null) {
    if ($opts === null) {
      $opts = array();
    }

    if (!file_exists($this->_outPath)) {
      mkdir($this->_outPath, 0755, true);
    }

    $files = array(
      array(
        'src' => File::joinPaths('src', 'galleria.js'),
        'out' => 'galleria.js'
      )
    );

    $theme = 'classic';
    if (isset($opts['theme'])) {
      $theme = $opts['theme'];
    }

    if (!array_key_exists($theme, self::$_themeFiles)) {
      throw new Exception("Unrecognized theme: $theme");
    }

    $files = array_merge($files, self::$_themeFiles[$theme]);

    foreach ($files AS $file) {
      $fileSrc = File::joinPaths($this->_srcPath, $file['src']);
      $fileOut = File::joinPaths($this->_outPath, $file['out']);

      $fileOutDir = dirname($fileOut);
      if (!file_exists($fileOutDir)) {
        mkdir($fileOutDir, 0755, true);
      }

      copy($fileSrc, $fileOut);
    }
  }

  public function compile(array $opts = null) {
  }

  public function inc(array $opts = null) {
    Element::js("$this->_webOut/galleria.js")->addToHead();
  }
}
