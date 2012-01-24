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

  const DATE_JS = 'datejs';
  const FILE_UPLOADER = 'file-uploader';
  const GALLERIA = 'galleria';
  const JQUERY_COOKIE = 'jquery-cookie';
  //const JQUERY_FILE_UPLOADER = 'jquery-file-uploader';
  const JQUERY_OPENID = 'jquery-openid';
  const JQUERY_SELECTBOX = 'jquery-selectBox';
  const JQUERY_UI = 'jquery-ui';
  const JQUERY_UI_TIMEPICKER = 'jQuery-Timepicker';
  const jWYSIWYG = 'jwysiwyg';

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
    $files = self::getFiles($lib, $opts);

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
  public static function getFiles($lib, array $opts = null) {
    $pathInfo = Conductor::getPathInfo();

    $libDir = null;
    $scripts = array();
    $sheets = array();
    $images = array();
    $external = array();

    switch ($lib) {
      case self::FILE_UPLOADER:
      $libDir = 'file-uploader';
      $scripts[] = array(
        'src' => 'client/fileuploader.js',
        'out' => 'fileuploader.js'
      );
      $sheets[] = array(
        'src' => 'client/fileuploader.css',
        'out' => 'fileuploader.css'
      );
      $images[] = array(
        'src' => 'client/loading.gif',
        'out' => 'loading.gif'
      );
      break;

      case self::GALLERIA:
      $libDir = 'galleria';
      $scripts[] = array(
        'src' => 'src/galleria.js',
        'out' => 'galleria.js'
      );
      $scripts[] = array(
        'src' => 'src/themes/classic/galleria.classic.js',
        'out' => 'themes/classic/galleria.classic.js',
        'static' => true
      );
      $sheets[] = array(
        'src' => 'src/themes/classic/galleria.classic.css',
        'out' => 'themes/classic/galleria.classic.css',
        'static' => true
      );
      $images[] = array(
        'src' => 'src/themes/classic/classic-loader.gif',
        'out' => 'themes/classic/classic-loader.gif'
      );
      $images[] = array(
        'src' => 'src/themes/classic/classic-map.png',
        'out' => 'themes/classic/classic-map.png'
      );
      break;

      case self::JQUERY_COOKIE:
      $libDir = 'jquery-cookie';
      $scripts[] = 'jquery.cookie.js';
      break;

      case self::JQUERY_OPENID:
      $libDir = 'jquery-openid';
      $scripts[] = 'jquery.openid.js';
      $sheets[] = 'openid.css';

      // FIXME We can use images for now since they are treated how we want the
      //       html file to be treated.
      // TODO Create a dedicated spot for html resources or combine them with
      //      images by giving images a better name (e.g. static)
      $images[] = 'login.html';

      $images[] = 'images/fadegrey.png';
      $images[] = 'images/big/yahoo.png';
      $images[] = 'images/big/livejournal.png';
      $images[] = 'images/big/hyves.png';
      $images[] = 'images/big/blogger.png';
      $images[] = 'images/big/orange.png';
      $images[] = 'images/big/google.png';
      $images[] = 'images/big/myspace.png';
      $images[] = 'images/big/wordpress.png';
      $images[] = 'images/big/aol.png';
      $images[] = 'images/big/openid.png';
      break;

      case self::JQUERY_SELECTBOX:
      $libDir = 'jquery-selectBox';
      $scripts[] = 'jquery.selectBox.min.js';
      $sheets[] = 'jquery.selectBox.css';
      $images[] = 'jquery.selectBox-arrow.gif';
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

      case self::jWYSIWYG:
      $libDir = 'jwysiwyg';
      $scripts[] = 'jquery.wysiwyg.js';
      $sheets[] = 'jquery.wysiwyg.css';
      $images[] = 'jquery.wysiwyg.bg.png';
      $images[] = 'jquery.wysiwyg.gif';
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
   * TODO Remove the pathInfo argument
   *
   * @param mixed $libs Either the name of a single library to include or an
   *   array of library names.
   * @param WebSitePathInfo $pathInfo
   * @param array $opts Optional array of library specific options for each of
   *   the included libs.  The array is expected to be index by lib name.
   */
  public static function includeLibs($libs, array $opts = null) {

    if (!is_array($libs)) {
      $libs = array($libs);
    }

    $libs = array_diff($libs, self::$_included);

    foreach ($libs AS $lib) {
      $libOpts = is_array($opts) && isset($opts[$lib])
        ? $opts[$lib]
        : null;

      self::includeLib($lib, $libOpts);
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
  public static function includeLib($lib, array $opts = null) {

    if (in_array($lib, self::$_included)) {
      return;
    }

    $files = self::getFiles($lib, $pathInfo, $opts);

    ResourceIncluder::inc($files);
    self::$_included[] = $lib;
  }
}
