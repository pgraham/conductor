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

use \conductor\Conductor;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;

use \reed\WebSitePathInfo;

/**
 * This class provides functionality for including any available 3rd party
 * Javascript library in the page.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JsLibIncluder {

  /* Array of libraries that have already been included in the page. */
  private static $_included = array();

  /**
   * Include the files for the given libraries.  Any that have already been
   * included will be silently ignored.
   *
   * @param mixed $libs Either the name of a single library to include or an
   *   array of library names.
   */
  public static function includeLibs($libs) {
    if (!is_array($libs)) {
      $libs = array($libs);
    }

    $libs = array_diff($libs, array_intersect($libs, self::$_included));

    $pathInfo = Conductor::$config['pathInfo'];
    foreach ($libs AS $lib) {
      $libDir = null;

      switch ($lib) {

        case 'jquery-ui':
        $includer = new JQueryUiIncluder($pathInfo);
        $includer->addToHead();
        break;

        case 'jquery-timepicker':
        $libDir  = 'jQuery-Timepicker';
        $scripts = array('jquery-ui-timepicker-addon.js');
        $sheets  = array('jquery-ui-timepicker-addon.css');
        break;

        // TODO - Update this class to use Resource instances to include scripts
        //        so that an output directory other than build can be specified.
        //        This may also require an update to the Resource class.
        case 'datejs':
        $libDir  = 'datejs';
        $scripts = array('build/date.js');
        $sheets  = array();
        break;

        default:
        assert("false; /* Unrecognized library: $lib */");
        continue;
      }

      if ($libDir !== null) {
        $includer = new JsLibIncluder($pathInfo, $libDir, $scripts, $sheets);
        $includer->addToHead();
      }

      self::$_included[] = $lib;
    }
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_scripts = array();
  private $_sheets = array();

  protected function __construct(WebSitePathInfo $pathInfo, $libDir,
      array $scripts, array $sheets)
  {
    $srcPath = $pathInfo->getLibPath() . "/jslib/$libDir";

    $webTarget = $pathInfo->getWebTarget();
    $cssOutputPath = $webTarget . '/css';
    $jsOutputPath = $webTarget . '/js';

    if (defined('DEBUG') && DEBUG === true) {
      $this->_compile($pathInfo, $srcPath, $jsOutputPath, $scripts);
      $this->_compile($pathInfo, $srcPath, $cssOutputPath, $sheets);
    }

    foreach ($scripts AS $script) {
      $scriptPath = $jsOutputPath . '/' . $script;

      $webPath = $pathInfo->fsToWeb($scriptPath);
      $this->_scripts[] = new Javascript($webPath);
    }

    foreach ($sheets AS $sheet) {
      $sheetPath = $cssOutputPath . '/' . $sheet;

      $webPath = $pathInfo->fsToWeb($sheetPath);
      $this->_sheets[] = new StyleSheet($webPath);
    }
  }

  public function addToHead() {
    foreach ($this->_scripts AS $script) {
      $script->addToHead();
    }
    foreach ($this->_sheets AS $sheet) {
      $sheet->addToHead();
    }
  }

  private function _compile($pathInfo, $srcPath, $outputPath, $files) {
    foreach ($files AS $file) {
      $fullSrcPath = $srcPath . '/' . $file;

      // Make sure that the output directory exists
      $fullOutputPath = $outputPath . '/' . dirname($file);
      if (!file_exists($fullOutputPath)) {
        mkdir($fullOutputPath, 0755, true);
      }

      copy($fullSrcPath, $fullOutputPath . '/' . basename($file));
    }
  }
}
