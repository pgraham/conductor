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
use \conductor\ResourceSet;
use \conductor\ResourceIncluder;
use \oboe\head\Javascript;
use \oboe\head\StyleSheet;
use \reed\WebSitePathInfo;

/**
 * Static interface for working with 3rd party javascript libraries.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JsLib {

  /*
   * ===========================================================================
   * Supported libs
   * ===========================================================================
   */

  const JQUERY_COOKIE = 'jquery-cookie';
  const JQUERY_UI = 'jquery-ui';
  const JQUERY_UI_TIMEPICKER = 'jquery-ui-timepicker';
  const DATE_JS = 'datejs';

  /* Array of libraries that have already been included in the page. */
  private static $_included = array();

  /**
   * Copy necessary files for the specified library to the output directory
   * specified by the given WebSitePathInfo object.
   *
   * @param string $lib The name of the library to compile.
   * @param WebSitePathInfo $pathInfo
   * @param array $opts Array of library specific options
   */
  public static function compile($lib, WebSitePathInfo $pathInfo,
      array $opts = null
  ) {
    $files = self::getFiles($lib, $pathInfo, $opts);

    ResourceIncluder::compile($files);
  }

  /**
   * Retrieve the set of resources required by the specified library.
   *
   * @param string $lib The name of the library for which to retrieve a resource
   *   set
   * @param WebSitePathInfo $pathInfo
   * @param array $opts Array of library specific options
   * @return array Containing three indexed, 'scripts', 'sheets' and 'images'.
   */
  public static function getFiles($lib, WebSitePathInfo $pathInfo,
      array $opts = null
  ) {
    $libDir = null;
    $scripts = array();
    $sheets = array();
    $images = array();
    $external = array();

    switch ($lib) {
      case self::JQUERY_COOKIE:
      $libDir = 'jquery-cookie';
      $scripts[] = 'jquery.cookie.js';
      break;

      case self::JQUERY_UI:
      $theme = is_array($opts) && isset($opts['theme'])
        ? $opts['theme']
        : null;
      $themeOnly = is_array($opts) && isset($opts['theme-only'])
        ? (boolean) $opts['theme-only']
        : false;
      $noTheme = !$themeOnly && is_array($opts) && isset($opts['no-theme'])
        ? (boolean) $opts['no-theme']
        : false;

      $libDir   = 'jquery-ui';
      $scripts  = !$themeOnly
        ? JQueryUiFiles::getScripts($theme, $pathInfo)
        : array();
      $sheets   = !$noTheme
        ? JQueryUiFiles::getSheets($theme, $pathInfo)
        : array();
      $images   = !$noTheme
        ? JQueryUiFiles::getImages($theme, $pathInfo)
        : array();
      $external = !$themeOnly
        ?JQueryUiFiles::getExternal()
        : array();
      break;

      case self::JQUERY_UI_TIMEPICKER:
      $libDir    = 'jQuery-Timepicker';
      $scripts[] = 'jquery-ui-timepicker-addon.js';
      $sheets[]  = 'jquery-ui-timepicker-addon.css';
      break;

      case self::DATE_JS:
      $libDir    = 'datejs';
      $scripts[] = array( 'src' => 'build/date.js', 'out' => 'date.js' );
      break;

      default:
      assert("false; /* Unrecognized library: $lib */");
    }

    $srcPath = $pathInfo->getLibPath() . "/jslib/$libDir";
    $resources = new ResourceSet($srcPath, $libDir);
    $resources->addExternal($external);
    $resources->setImages($images);
    $resources->setScripts($scripts);
    $resources->setSheets($sheets);
    return $resources;
  }

  /**
   * Include the files for the given libraries.  Any that have already been
   * included will be silently ignored.
   *
   * @param mixed $libs Either the name of a single library to include or an
   *   array of library names.
   * @param WebSitePathInfo $pathInfo
   * @param array $opts Optional array of library specific options for each of
   *   the included libs.  The array is expected to be index by lib name.
   */
  public static function includeLibs($libs, WebSitePathInfo $pathInfo,
      array $opts = null
  ) {

    if (!is_array($libs)) {
      $libs = array($libs);
    }

    $libs = array_diff($libs, self::$_included);

    foreach ($libs AS $lib) {
      $libOpts = is_array($opts) && isset($opts[$lib])
        ? $opts[$lib]
        : null;

      self::includeLib($lib, $pathInfo, $libOpts);
    }
  }

  /**
   * Include the files for the given library.  If the library has already been
   * included it will be silently ignored.
   *
   * @param string $lib The name of the library to include.
   * @param WebSitePathInfo $pathInfo
   * @param array $opts Optional array of library specific options.
   */
  public static function includeLib($lib, WebSitePathInfo $pathInfo,
      array $opts = null
  ) {

    if (Conductor::isDebug()) {
      self::compile($lib, $pathInfo, $opts);
    }

    $files = self::getFiles($lib, $pathInfo, $opts);

    ResourceIncluder::inc($files);
    self::$_included[] = $lib;
  }
}
