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

use \DirectoryIterator;

use \Exception;

/**
 * This class encapsulates file lists for jQuery UI as well as logic for
 * building file lists for different themes.  The file lists that are generated
 * by this class depend on the theme that is passed to each function.  If no
 * theme is specified then the default theme is used.  If a theme is specified,
 * then the file lists returned assume that the theme is a theme-roller theme.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JQueryUiFiles {

  public static $scripts = array(
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
    'ui/jquery.ui.menu.js',
    'ui/jquery.ui.dialog.js',
    'ui/jquery.ui.tabs.js',
    'ui/jquery.ui.spinner.js',
    'ui/jquery.ui.datepicker.js',
    'ui/jquery.ui.slider.js',
  );

  public static $sheets = array(
    'themes/base/jquery.ui.base.css',
    'themes/base/jquery.ui.theme.css'
  );

  public static function getExternal() {
    return array();
  }

  public static function getImages($theme, $pathInfo) {
    $images = array();

    if ($theme === null) {

      $imgRel = 'themes/base/images';
      $imgPath = "{$pathInfo->getLibPath()}/jslib/jquery-ui/$imgRel";
      $imgDir = new DirectoryIterator($imgPath);

      foreach ($imgDir AS $img) {
        if ($img->isDot() || $img->isDir()) {
          continue;
        }
        $images[] = "$imgRel/{$img->getBasename()}";
      }

    // If theme is specified as false then the user is using their own theme
    } else if ($theme === false) {
      return array();
    } else {
      $themeDir  = self::_getThemeDir($theme, $pathInfo);
      $themeName = self::_getThemeName($theme);

      $imgPath = "$themeDir/images";
      $imgDir = new DirectoryIterator("$themeDir/images");

      foreach ($imgDir AS $img) {
        if ($img->isDot() || $img->isDir()) {
          continue;
        }

        $images[] = array(
          'base' => $imgPath,
          'src'  => $img->getBasename(),
          'out'  => "themes/$themeName/images/{$img->getBasename()}"
        );
      }
    }

    return $images;
  }

  public static function getScripts($theme, $pathInfo) {
    return self::$scripts;
  }

  public static function getSheets($theme, $pathInfo) {
    if ($theme === null) {
      return self::$sheets;
    } else if ($theme === false) {
      // Specifying theme to false
      return array(
        'themes/base/jquery.ui.base.css',
      );
    } else {
      $sheets = array();

      $themeDir = self::_getThemeDir($theme, $pathInfo);
      $themeName = self::_getThemeName($theme);

      $sheets[] = array(
        'base' => $themeDir,
        'src'  => 'jquery-ui.css',
        'out'  => "themes/$themeName/jquery-ui.css"
      );

      return $sheets;
    }
  }

  private static function _getThemeDir($theme, $pathInfo) {
    if (file_exists($theme)) {
      $themeDir = $theme;
    } else {
      $themeDir = $pathInfo->getLibPath()
        . "/conductor/src/resources/$theme-theme";

      if (!file_exists($theme) {
        throw new Exception("Specified theme does not exist: $theme.  It was"
          . " expected to be found at $themeDir");
      }
    }

    return $themeDir;
  }

  private static function _getThemeName($theme) {
    if (file_exists($theme)) {
      return basename($theme);
    } else {
      return $theme;
    }
  }
}
