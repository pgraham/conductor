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

  const SRC_PATH = '/home/pgraham/projects/jquery-ui';

  public static $coreScripts = Array(
    '/ui/jquery.ui.core.js',
    '/ui/jquery.ui.widget.js',
    '/ui/jquery.ui.mouse.js',
    '/ui/jquery.ui.draggable.js',
    '/ui/jquery.ui.resizable.js',
    '/ui/jquery.ui.selectable.js',
    '/ui/jquery.ui.sortable.js',
    '/ui/jquery.effects.core.js'
  );

  public static $widgets = Array(
    '/ui/jquery.ui.menu.js'
  );

  private $_cssOutputPath;

  private $_jsOutputPath;

  private $_pathInfo;

  private $_scripts = Array();
  private $_styleSheet;

  public function __construct(WebSitePathInfo $pathInfo) {
    $webTarget = $pathInfo->getWebTarget();

    $this->_cssOutputPath = $webTarget . '/css/ui';
    $this->_jsOutputPath = $webTarget . '/js/ui';
    $this->_pathInfo = $pathInfo;

    if (defined('DEBUG') && DEBUG === true) {
      // Make sure directories exist
      $jsOut = $webTarget . '/js/ui';
      if (!file_exists($jsOut)) {
        mkdir($jsOut, 0755, true);
      }

      $cssOut = $webTarget . '/css/ui';
      if (!file_exists($cssOut)) {
        mkdir($cssOut, 0755, true);
      }

      $imgOut = $webTarget . '/css/ui/images';
      if (!file_exists($imgOut)) {
        mkdir($imgOut, 0755, true);
      }

      // Copy javascripts into web writable
      $jsDir = new DirectoryIterator(self::SRC_PATH . '/ui');
      foreach ($jsDir AS $file) {
        if ($file->isDot() || $file->isDir()) {
          continue;
        }

        copy($file->getPathName(), $jsOut . '/' . $file->getFileName());
      }

      // Copy stylesheets and images web writable
      $cssDir = new DirectoryIterator(self::SRC_PATH . '/themes/base');
      foreach ($cssDir AS $file) {
        if ($file->isDot() || $file->isDir()) {
          continue;
        }

        copy($file->getPathName(), $cssOut . '/' . $file->getFileName());
      }

      // Copy images into web writable
      $imgDir = new DirectoryIterator(self::SRC_PATH . '/themes/base/images');
      foreach ($imgDir AS $file) {
        if ($file->isDot() || $file->isDir()) {
          continue;
        }

        copy($file->getPathName() , $imgOut . '/' . $file->getFileName());
      }
    }

    foreach (self::$coreScripts AS $script) {
      $this->_addScript($script);
    }

    foreach (self::$widgets AS $script) {
      $this->_addScript($script);
    }

    $this->_addStyleSheet('/themes/base/jquery.ui.all.css');
  }

  public function getScripts() {
    return $this->_scripts;
  }

  public function getStyleSheet() {
    return $this->_styleSheet;
  }

  private function _addScript($script) {
    $scriptName = basename($script);
    $scriptPath = $this->_jsOutputPath . '/' . $scriptName;

    $webPath = $this->_pathInfo->fsToWeb($scriptPath);
    $this->_scripts[] = new Javascript($webPath);
  }

  private function _addStyleSheet($styleSheet) {
    $styleSheetName = basename($styleSheet);
    $styleSheetPath = $this->_cssOutputPath . '/' . $styleSheetName;

    $webPath = $this->_pathInfo->fsToWeb($styleSheetPath);
    $this->_styleSheet = new StyleSheet($webPath);
  }
}
