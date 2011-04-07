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
 * @package conductor/script
 */
namespace conductor\script;

use \DirectoryIterator;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;

use \reed\WebSitePathInfo;

/**
 * This class includes the necessary JQueryUiScripts for a set a specified
 * components.  If debug mode is on then the scripts are copied from the
 * current development repository found at the specified path.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/script
 */
class JQueryUiIncluder {

  // TODO - Figure out why this isn't working
  //public static $SRC_PATH = __DIR__ . '/../../../jquery-ui';

  public static $scripts = array(
    '/ui/jquery.ui.core.js',
    '/ui/jquery.ui.widget.js',
    '/ui/jquery.ui.mouse.js',
    '/ui/jquery.ui.draggable.js',
    '/ui/jquery.ui.position.js',
    '/ui/jquery.ui.resizable.js',
    '/ui/jquery.ui.selectable.js',
    '/ui/jquery.ui.sortable.js',
    '/ui/jquery.effects.core.js',
    '/ui/jquery.ui.menu.js',
    '/ui/jquery.ui.dialog.js',
    '/grid-datamodel/dataitem.js',
    '/grid-datamodel/datastore.js',
    '/grid-datamodel/datasource.js',
    '/grid-datamodel/grid.js'
  );

  public static $styleSheets = array(
    '/themes/base/jquery.ui.base.css',
    '/themes/base/jquery.ui.core.css',
    '/themes/base/jquery.ui.theme.css',
    '/themes/base/jquery.ui.accordion.css',
    '/themes/base/jquery.ui.button.css',
    '/themes/base/jquery.ui.autocomplete.css',
    '/themes/base/jquery.ui.datepicker.css',
    '/themes/base/jquery.ui.dialog.css',
    '/themes/base/jquery.ui.menu.css',
    '/themes/base/jquery.ui.progressbar.css',
    '/themes/base/jquery.ui.resizable.css',
    '/themes/base/jquery.ui.selectable.css',
    '/themes/base/jquery.ui.slider.css',
    '/themes/base/jquery.ui.spinner.css',
    '/themes/base/jquery.ui.tabs.css',
    '/themes/base/jquery.ui.tooltip.css',
    '/grid-datamodel/grid.css'
  );

  private $_cssOutputPath;
  private $_jsOutputPath;
  private $_pathInfo;

  private $_scripts = array();
  private $_styleSheets = array();

  public function __construct(WebSitePathInfo $pathInfo) {
    $this->_srcPath = realpath(__DIR__ . '/../../../jquery-ui');

    $webTarget = $pathInfo->getWebTarget();

    $this->_cssOutputPath = $webTarget . '/css';
    $this->_jsOutputPath = $webTarget . '/js';
    $this->_pathInfo = $pathInfo;

    if (defined('DEBUG') && DEBUG === true) {
      // Make sure image directory exists
      $imgOut = $webTarget . '/css/themes/base/images';
      if (!file_exists($imgOut)) {
        mkdir($imgOut, 0755, true);
      }

      // Copy javascripts into web writable
      foreach (self::$scripts AS $script) {
        $scriptPath = $this->_srcPath . $script;
        
        // Make that the output directory exists
        $outputPath = $this->_jsOutputPath . dirname($script);
        if (!file_exists($outputPath)) {
          mkdir($outputPath, 0755, true);
        }

        copy($scriptPath, $outputPath . '/' . basename($scriptPath));
      }

      // Copy stylesheets and images web writable
      foreach (self::$styleSheets AS $styleSheet) {
        $cssPath = $this->_srcPath . $styleSheet;

        // Make sure that the output directory exists
        $outputPath = $this->_cssOutputPath . dirname($styleSheet);
        if (!file_exists($outputPath)) {
          mkdir($outputPath, 0755, true);
        }

        copy($cssPath, $outputPath . '/' . basename($styleSheet));
      }

      // Copy images into web writable
      $imgDir = new DirectoryIterator($this->_srcPath . '/themes/base/images');
      foreach ($imgDir AS $file) {
        if ($file->isDot() || $file->isDir()) {
          continue;
        }

        copy($file->getPathName() , $imgOut . '/' . $file->getFileName());
      }
    }

    // The grid widget relies on the Templates plugin.
    $this->_scripts[] = new Javascript(
      'http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.js');

    foreach (self::$scripts AS $script) {
      $scriptPath = $this->_jsOutputPath . $script;

      $webPath = $this->_pathInfo->fsToWeb($scriptPath);
      $this->_scripts[] = new Javascript($webPath);
    }


    foreach (self::$styleSheets AS $styleSheet) {
      $styleSheetPath = $this->_cssOutputPath . $styleSheet;

      $webPath = $this->_pathInfo->fsToWeb($styleSheetPath);
      $this->_styleSheets[] = new StyleSheet($webPath);
    }
  }

  public function getScripts() {
    return $this->_scripts;
  }

  public function getStyleSheets() {
    return $this->_styleSheets;
  }
}
