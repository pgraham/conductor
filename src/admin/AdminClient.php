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
 * @package conductor/admin
 */
namespace conductor\admin;

use \conductor\generator\ModelInfoSet;
use \conductor\Conductor;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;

use \reed\FsToWebPathConverter;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates the client side component of conductor's admin
 * interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/admin
 */
class AdminClient {

  const FONT_PATH = 'http://fonts.googleapis.com/css?family=Allerta';

  private $_js;
  private $_css;

  /**
   * Create a new Javascript element for the conductor admin client.
   *
   * If debug mode is enabled, then the script will generated using the site's
   * specified model classes.
   *
   * @param array $modelNames Array of models for which to build the client
   */
  public function __construct(Array $modelNames, WebSitePathInfo $pathInfo) {
    $webWrite = $pathInfo->getWebTarget();
    $webPath = $pathInfo->getWebAccessibleTarget();

    if (defined('DEBUG') && DEBUG === true) {
      $generator = new AdminGenerator(new ModelInfoSet($modelNames));
      $generator->generate($pathInfo);

      if (!file_exists($webWrite . '/css')) {
        mkdir($webWrite . '/css', 0755, true);
      }
      copy(
        __DIR__ . '/conductor-admin.css',
        $webWrite . '/css/conductor-admin.css');
    }

    $this->_js = new Javascript($webPath . '/js/conductor-admin.js');
    $this->_css = new StyleSheet($webPath . '/css/conductor-admin.css');
  }

  public function getScript() {
    return $this->_js;
  }

  public function getStyleSheets() {
    return Array( $this->_css, new StyleSheet(self::FONT_PATH) );
  }
}
