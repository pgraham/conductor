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

use \conductor\jslib\JQueryUi;
use \DirectoryIterator;

/**
 * This class handles compiler JavaScript libraries.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JslibCompiler {

  private $_compressed;

  public function __construct($compressed = false) {
    $this->_compressed = $compressed;
  }

  public function compile($jslibName, $pathInfo) {
    $this->_ensureTarget($pathInfo, $jslibName);

    switch ($jslibName) {

      case 'datejs':
      $this->compileDateJs($pathInfo);
      break;

      case 'jquery-cookie':
      $this->compileJQueryCookie($pathInfo);
      break;

      case 'jquery-ui':
      $this->compileJQueryUi($pathInfo);
      break;

      case 'file-uploader':
      $this->compileFileUploader($pathInfo);
      break;

      case 'galleria':
      $this->compileGalleria($pathInfo);
      break;

      case 'raphael':
      $this->compileRaphael($pathInfo);
      break;

      // Default, simply copy the library's source file to the target
      default:
      // TODO
      break;
    }
  }

  protected function compileDateJs($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/datejs";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/datejs";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }

    copy("$jslibSrc/build/date.js", "$jslibOut/date.js");
  }

  protected function compileFileUploader($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/file-uploader";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/file-uploader";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }

    copy("$jslibSrc/client/fileuploader.js", "$jslibOut/fileuploader.js");
    copy("$jslibSrc/client/fileuploader.css", "$jslibOut/fileuploader.css");
    copy("$jslibSrc/client/loading.gif", "$jslibOut/loading.gif");
  }

  protected function compileGalleria($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/galleria/src";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/galleria";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }
    copy("$jslibSrc/galleria.js", "$jslibOut/galleria.js");

    $themeSrc = "$jslibSrc/themes/classic";
    $themeOut = "$jslibOut/themes/classic";
    if (!file_exists($themeOut)) {
      mkdir($themeOut, 0755, true);
    }
    copy("$themeSrc/galleria.classic.js", "$themeOut/galleria.classic.js");
    copy("$themeSrc/galleria.classic.css", "$themeOut/galleria.classic.css");
    copy("$themeSrc/classic-loader.gif", "$themeOut/classic-loader.gif");
    copy("$themeSrc/classic-map.png", "$themeOut/classic-map.png");
  }

  protected function compileJQueryCookie($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/jquery-cookie";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/jquery-cookie";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }
    copy("$jslibSrc/jquery.cookie.js", "$jslibOut/jquery.cookie.js");
  }

  protected function compileJQueryUi($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/jquery-ui";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/jquery-ui";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }

    // Copy external dependencies into output dir
    $extSrc = "$jslibSrc/external";
    $extOut = "$jslibOut/external";

    if (!file_exists($extOut)) {
      mkdir($extOut, 0755, true);
    }

    copy("$extSrc/globalize.js", "$extOut/globalize.js");

    // Collect the javascript source into a single file
    $jsAll = array();
    $scripts = array(
      'ui/jquery.ui.core.js',
      'ui/jquery.ui.widget.js',
      'ui/jquery.ui.mouse.js',
      'ui/jquery.ui.draggable.js',
      'ui/jquery.ui.position.js',
      'ui/jquery.ui.resizable.js',
      'ui/jquery.ui.selectable.js',
      'ui/jquery.ui.sortable.js',
      'ui/jquery.effects.core.js',
      'ui/jquery.ui.button.js',
      'ui/jquery.ui.datepicker.js',
      'ui/jquery.ui.dialog.js',
      'ui/jquery.ui.menu.js',
      'ui/jquery.ui.spinner.js',
      'ui/jquery.ui.slider.js',
      'ui/jquery.ui.tabs.js',
      'ui/jquery.ui.tooltip.js'
    );
    foreach ($scripts as $js) {
      $jsAll[] = file_get_contents("$jslibSrc/$js");
    }
    file_put_contents("$jslibOut/jquery.ui.js", implode("\n", $jsAll));

    // Collect the css source into a single file
    $cssAll = array();
    $cssDir = new DirectoryIterator("$jslibSrc/themes/base");
    $excluded = array(
      'jquery.ui.base.css',
      'jquery.ui.all.css',
      'jquery.ui.theme.css'
    );
    foreach ($cssDir as $css) {
      if ($css->isDot() || $css->isDir()) {
        continue;
      }

      $fname = $css->getFilename();
      if (in_array($fname, $excluded)) {
        continue;
      }

      $cssAll[] = file_get_contents($css->getPathname());
    }
    file_put_contents("$jslibOut/jquery.ui.css", implode("\n", $cssAll));

    // Default theme, it will get compiled, but may never be used
    $themeSrc = "$jslibSrc/themes/base";
    $themeOut = "$jslibOut/themes/base";
    $this->_compileTheme($themeSrc, $themeOut);

    // -- JQueryUI themes may eventually only be a part of a larger theme,
    //    in which case this should be moved into a theme specific portion
    //    of compilation

    // Compile predefined conductor themes
    $this->_compileThemeDir("$pathInfo[lib]/conductor/src/resources/themes",
      "$jslibOut/themes");

    // Compile site specific themes
    $this->_compileThemeDir("$pathInfo[src]/themes", "$jslibOut/themes");
  }

  protected function compileRaphael($pathInfo) {
    $jslibSrc = "$pathInfo[lib]/jslib/raphael";
    $jslibOut = "$pathInfo[target]/htdocs/jslib/raphael";

    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }

    copy("$jslibSrc/raphael-min.js", "$jslibOut/raphael.js");
  }

  private function _compileTheme($src, $out) {
    if (!file_exists($out)) {
      mkdir($out, 0755, true);
    }
    copy(
      "$src/jquery.ui.theme.css",
      "$out/jquery.ui.theme.css");

    if (!file_exists("$out/images")) {
      mkdir("$out/images");
    }

    $imgs = new DirectoryIterator("$src/images");
    foreach ($imgs as $img) {
      if ($img->isDot() || $img->isDir()) {
        continue;
      }

      copy(
        "$src/images/{$img->getFilename()}",
        "$out/images/{$img->getFilename()}");
    }
  }

  private function _compileThemeDir($dir, $out) {
    if (!file_exists($dir)) {
      return;
    }

    $themes = new DirectoryIterator($dir);
    foreach ($themes as $theme) {
      if ($theme->isDot() || !$theme->isDir()) {
        continue;
      }

      $src = "$dir/{$theme->getBasename()}";
      $out = "$out/{$theme->getBasename()}";
      $this->_compileTheme($src, $out);
    }
  }

  private function _ensureTarget($pathInfo, $jslibName) {
    $jslibTarget = "$pathInfo[target]/htdocs/jslib/$jslibName";
  }
}
