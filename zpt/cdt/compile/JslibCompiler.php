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
namespace zpt\cdt\compile;

use \zpt\cdt\compile\resource\ResourceCompiler;
use \DirectoryIterator;

/**
 * This class handles compiler JavaScript libraries.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JslibCompiler {

  public function compile($jslibPath, $pathInfo) {
    $jslibName = basename($jslibPath);

    $jslibOut = "$pathInfo[target]/htdocs/jslib/$jslibName";
    if (!file_exists($jslibOut)) {
      mkdir($jslibOut, 0755, true);
    }

    switch ($jslibName) {

      case 'file-uploader':
      $this->compileFileUploader($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'galleria':
      $this->compileGalleria($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'highlight':
      $this->simpleCompile($jslibPath, $jslibOut);
      break;

      case 'jquery-cookie':
      $this->compileJQueryCookie($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'jquery-openid':
      $this->compileJQueryOpenId($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'jquery-selectBox':
      $this->compileJQuerySelectBox($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'jquery-ui':
      $this->compileJQueryUi($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'jwysiwyg':
      $this->compileJWysiwyg($pathInfo, $jslibPath, $jslibOut);
      break;

      case 'webshims':
      $this->compileWebshims($pathInfo, $jslibPath, $jslibOut);
      break;

      // Default, simply copy the library's source files to the target
      default:
      // TODO Abstract _compileResourceDir into a class and reuse it to compile
      //      the jslib
      break;
    }
  }

  protected function compileFileUploader($pathInfo, $jslibSrc, $jslibOut) {
    $this->copyFiles("$jslibSrc/client", $jslibOut, array(
      'fileuploader.js',
      'fileuploader.css',
      'loading.gif'
    ));
  }

  protected function compileGalleria($pathInfo, $jslibSrc, $jslibOut) {
    $this->copyFiles("$jslibSrc/src", $jslibOut, array('galleria.js'));

    $themesDir = new DirectoryIterator("$jslibSrc/src/themes");
    foreach ($themesDir as $theme) {
      if ($theme->isDot() || !$theme->isDir()) {
        continue;
      }

      $themeName = $theme->getBasename();
      
      $themeSrc = $theme->getPathname();
      $themeOut = "$jslibOut/themes/$themeName";
      if (!file_exists($themeOut)) {
        mkdir($themeOut, 0755, true);
      }

      $this->copyFiles($themeSrc, $themeOut, array(
        "galleria.$themeName.js",
        "galleria.$themeName.css",
        "$themeName-loader.gif",
        "$themeName-map.png"
      ));
    }
  }

  protected function compileJQueryCookie($pathInfo, $jslibSrc, $jslibOut) {
    copy("$jslibSrc/jquery.cookie.js", "$jslibOut/jquery.cookie.js");
  }

  protected function compileJQueryOpenId($pathInfo, $jslibSrc, $jslibOut) {
    $this->copyFiles($jslibSrc, $jslibOut, array(
      'jquery.openid.js',
      'openid.css',
      'login.html',
      'images/fadegrey.png',
      'images/big/yahoo.png',
      'images/big/livejournal.png',
      'images/big/hyves.png',
      'images/big/blogger.png',
      'images/big/orange.png',
      'images/big/google.png',
      'images/big/myspace.png',
      'images/big/wordpress.png',
      'images/big/aol.png',
      'images/big/openid.png'
    ));
  }

  protected function compileJQuerySelectBox($pathInfo, $jslibSrc, $jslibOut) {
    $this->copyFiles($jslibSrc, $jslibOut, array(
      'jquery.selectBox.min.js',
      'jquery.selectBox.css',
      'jquery.selectBox-arrow.gif'
    ));
  }

  protected function compileJQueryUi($pathInfo, $jslibSrc, $jslibOut) {
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
      'ui/jquery.ui.droppable.js',
      'ui/jquery.ui.position.js',
      'ui/jquery.ui.resizable.js',
      'ui/jquery.ui.selectable.js',
      'ui/jquery.ui.sortable.js',
      'ui/jquery.ui.effect.js',
      'ui/jquery.ui.accordion.js',
      'ui/jquery.ui.autocomplete.js',
      'ui/jquery.ui.button.js',
      'ui/jquery.ui.datepicker.js',
      'ui/jquery.ui.dialog.js',
      'ui/jquery.ui.menu.js',
      'ui/jquery.ui.progressbar.js',
      'ui/jquery.ui.slider.js',
      'ui/jquery.ui.spinner.js',
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
    $this->compileTheme($themeSrc, $themeOut);

    // -- JQueryUI themes may eventually only be a part of a larger theme,
    //    in which case this should be moved into a theme specific portion
    //    of compilation

    // Compile predefined conductor themes
    $this->compileThemeDir("$pathInfo[lib]/conductor/resources/themes",
      "$jslibOut/themes");

    // Compile site specific themes
    $this->compileThemeDir("$pathInfo[src]/themes", "$jslibOut/themes");
  }

  protected function compileJWysiwyg($pathInfo, $jslibSrc, $jslibOut) {
    $this->copyFiles($jslibSrc, $jslibOut, array(
      'jquery.wysiwyg.js',
      'jquery.wysiwyg.css',
      'jquery.wysiwyg.bg.png',
      'jquery.wysiwyg.gif'
    ));
  }

  protected function compileWebshims($pathInfo, $jslibSrc, $jslibOut) {
    $jslibSrc = "$jslibSrc/js-webshim/minified";
    
    $modernizrPath = "$jslibSrc/extras/modernizr-custom.js";
    $polyfillerPath = "$jslibSrc/polyfiller.js";
    $load = <<<LOAD

// Since jQuery 1.7+ is used there is no need to load the shiv for dynamic
// HTML5 element creation
window.html5 = { shivMethods: false};

$.webshims.polyfill('es5 geolocation json-storage');
LOAD;

    $modernizr = file_get_contents($modernizrPath);
    $polyfiller = file_get_contents($polyfillerPath);

    $script =  $modernizr . $polyfiller . $load;
    file_put_contents("$jslibOut/polyfiller.js", $script);

    // compile to copy $jslibSrc/shims
    $resourceCompiler = new ResourceCompiler();
    $resourceCompiler->compile("$jslibSrc/shims", "$jslibOut/shims");
  }

  private function compileTheme($src, $out) {
    if (!file_exists($out)) {
      mkdir($out, 0755, true);
    }
    copy(
      "$src/jquery.ui.theme.css",
      "$out/jquery.ui.theme.css");

    if (!file_exists("$out/images")) {
      mkdir("$out/images");
    }

    $this->copyDirectory("$src/images", "$out/images");
  }

  private function compileThemeDir($dir, $out) {
    if (!file_exists($dir)) {
      return;
    }

    $themes = new DirectoryIterator($dir);
    foreach ($themes as $theme) {
      if ($theme->isDot() || !$theme->isDir()) {
        continue;
      }

      $src = "$dir/{$theme->getBasename()}";
      $themeOut = "$out/{$theme->getBasename()}";
      $this->compileTheme($src, $themeOut);
    }
  }

  private function copyDirectory($src, $out) {
    if (!file_exists($src)) {
      return;
    }

    if (!file_exists($out)) {
      mkdir($out, 0755, true);
    }

    $files = new DirectoryIterator($src);
    foreach ($files as $file) {
      if (!$file->isDot() && !$file->isDir()) {
        copy($file->getPathname(), "$out/{$file->getFilename()}");
      }
    }
  }

  private function copyFiles($src, $out, array $files) {
    foreach ($files as $file) {
      if (file_exists("$src/$file")) {
        $dir = dirname("$out/$file");
        if (!file_exists($dir)) {
          mkdir($dir, 0755, true);
        }
        copy("$src/$file", "$out/$file");
      }
    }
  }

  /**
   * Do a simple compile which consists of a js and a css file named the same
   * as the lib and some optional files that are just simply copied from source
   * to distination.
   */
  private function simpleCompile($src, $out, array $files = null) {
    if ($files === null) {
      $files = array();
    }

    $jslib = basename($src);
    $files[] = "$jslib.js";
    $files[] = "$jslib.css";

    $this->copyFiles($src, $out, $files);
  }
}
