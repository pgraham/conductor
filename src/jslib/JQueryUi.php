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
use \reed\File;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates the files required for jQuery UI.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JQueryUi extends BaseLibrary {

  public function __construct(array $opts = null) {
    $this->init(JsLib::JQUERY_UI, $opts);
  }

  protected function getLinked($pathInfo, $devMode) {
    $linked = array(
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
      'themes/base/jquery.ui.base.css',
      'themes/base/jquery.ui.core.css',
      'themes/base/jquery.ui.accordion.css',
      'themes/base/jquery.ui.autocomplete.css',
      'themes/base/jquery.ui.button.css',
      'themes/base/jquery.ui.datepicker.css',
      'themes/base/jquery.ui.dialog.css',
      'themes/base/jquery.ui.grid.css',
      'themes/base/jquery.ui.menu.css',
      'themes/base/jquery.ui.menubar.css',
      'themes/base/jquery.ui.resizable.css',
      'themes/base/jquery.ui.progressbar.css',
      'themes/base/jquery.ui.selectable.css',
      'themes/base/jquery.ui.slider.css',
      'themes/base/jquery.ui.spinner.css',
      'themes/base/jquery.ui.tabs.css',
      'themes/base/jquery.ui.tooltip.css'
    );

    if (isset($this->opts['theme'])) {
      $theme = $this->opts['theme'];
      if (Resource::getResourceType($theme) === null) {
        // This is the name of a pre-defined theme, links it's files in
        $themeDir = $pathInfo->getLibPath() .
          "/conductor/src/resources/$theme-theme";

        if (!file_exists($theme)) {
          throw new Exception("Specified theme does not exist: $theme.  It was"
            . " expected to be found at $themeDir");
        }

        $linked[] = array(
          'src' => "$themeDir/jquery-ui.css",
          'out' => "themes/$theme/jquery-ui.css"
        );

        $imgs = new DirectoryIterator("$themeDir/images");
        foreach ($imgs AS $img) {
          if ($img->isDot() || $img->isDir()) {
            continue;
          }

          $linked[] = array(
            'src' => "$themeDir/images/{$img->getBasename()}",
            'out' => "themes/$theme/images/{$img->getBasename()}"
          );
        }
      } else {
        // The theme has been specified as a css file so it is a custom theme
        // that already lives in the document root, nothing to do here
      }

    } else {
      // Inlude the files for the default theme
      $linked[] = 'themes/base/jquery.ui.theme.css';

      $imgRel = 'themes/base/images';
      $imgPath = "{$pathInfo->getLibPath()}/jslib/jquery-ui/$imgRel";
      $imgDir = new DirectoryIterator($imgPath);

      foreach ($imgDir AS $img) {
        if ($img->isDot() || $img->isDir()) {
          continue;
        }
        $linked[] = "$imgRel/{$img->getBasename()}";
      }
      
    }

    return $linked;
  }

  protected function getIncluded($pathInfo, $devMode) {
    $included = array(
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
      'themes/base/jquery.ui.base.css'
    );

    if (isset($this->opts['theme'])) {
      $theme = $this->opts['theme'];
      if (Resource::getResourceType($theme) === null) {
        // This is the name of a pre-defined theme, links it's css file
        $included[] = "themes/$theme/jquery-ui.css";

      } else {
        // The theme has been specified as a css file so it is a custom theme
        // that already lives in the document root so include it directly
        // Themes specified in this way must be defined as absolute to the
        // document root.
        $included[] = $theme;
      }

    } else {
      $included[] = 'themes/base/jquery.ui.theme.css';
    }

    return $included;
  }
}
