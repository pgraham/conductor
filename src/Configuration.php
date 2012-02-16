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

use \conductor\config\ConfigurationValues;
use \conductor\config\Parser;

/**
 * This class encapsulates the configuration for a conductor site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Configuration {

  /**
   * Parse configuration from a given XML path.
   */
  public static function parse($configPath) {
    $parsed = Parser::parse($configPath);

    $config = new Configuration();

    $config->_pathInfo = $parsed['pathInfo'];
    $config->_db = $parsed['pdo'];
    $config->_dev = $parsed['debug'];
    $config->_host = $parsed['host'];
    $config->_pages = $parsed['pageCfg'];

    $config->_auth = new ConfigurationValues(array(
      'sessionTtl' => $parsed['sessionTtl']
    ));

    return $config;
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_db;
  private $_dev;
  private $_host;
  private $_pages;
  private $_pathInfo;
  private $_auth;

  protected function __construct() {}

  public function getAuthConfiguration() {
    return $this->_auth;
  }

  public function getClarinetConfiguration() {
    return array(
      'pdo' => $this->_db,
      'outputPath' => $this->_pathInfo->getTarget(),
      'debug' => $this->_dev
    );
  }

  public function getHostName() {
    return $this->_host;
  }

  public function getPage($pageId = null) {
    if ($pageId === null) {
      if (!isset($this->_pages['default'])) {
        return null;
      }

      $pageId = $this->_pages['default'];
    }

    if (isset($this->_pages['pages'][$pageId])) {
      return $this->_pages['pages'][$pageId];
    }
    return null;
  }

  public function getPathInfo() {
    return $this->_pathInfo;
  }

  public function getSiteNamespace() {
    return $this->_pathInfo->getSrcNs();
  }

  public function isDevMode() {
    return $this->_dev;
  }

}
