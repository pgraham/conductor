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
namespace conductor\config;

use \conductor\auth\SessionManager;
use \conductor\Exception;
use \reed\WebSitePathInfo;

/**
 * This class parses a conductor configuration XML file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Parser {

  /**
   * Parse the configuration XML file found at the given path and return the
   * results as an array.
   *
   * @param string $configPath Path to the web site's configuration file
   * @return array Array of configuration options
   */
  public static function parse($configPath) {
    $cfg = Array();

    // Any paths defined in the configuration file will be evaluated as relative
    // to the file.  This path is used to determine namespaces while scanning
    // model files so we need to resolve its 'real' path.
    $pathRoot = realpath(dirname($configPath));

    $xmlCfg = simplexml_load_file($configPath, 'SimpleXMLElement',
      LIBXML_NOCDATA);

    // Check for required items
    if (!isset($xmlCfg->db)) {
      throw new Exception('No database configuration found');
    }
    $cfg['pdo'] = Db::parse($xmlCfg->db, $pathRoot);

    // Set debug mode
    $cfg['debug'] = isset($xmlCfg->debug);

    // Set the web site's title
    if (isset($xmlCfg->title)) {
      $cfg['title'] = $xmlCfg->title->__toString();
    } else {
      $cfg['title'] = 'Powered by Conductor';
    }

    // Create an array of model files.  No parsing is actually done until
    // necessary
    if (isset($xmlCfg->models)) {
      $cfg['models'] = Model::parse($xmlCfg->models, $pathRoot);
    } else {
      $cfg['models'] = array();
    }

    // Create an array of pages if defined
    if (isset($xmlCfg->pages)) {
      $cfg['pageCfg'] = Page::parse($xmlCfg->pages, $pathRoot);
    } else {
      // TODO Should this be an error?
      $cfg['pageCfg'] = array();
    }

    if (isset($xmlCfg->services)) {
      $cfg['services'] = ServiceParser::parse($xmlCfg->services, $pathRoot);
    } else {
      $cfg['services'] = array();
    }

    if (isset($xmlCfg->pathInfo)) {
      $pathInfo = WebSitePathInfo::parse($xmlCfg->pathInfo, $pathRoot);
    } else {
      $pathInfo = WebSitPathInfo::parse($xmlCfg, $pathRoot);
    }

    $cfg['pathInfo'] = $pathInfo;
    if (isset($sourceNs)) {
      $cfg['basens'] = $sourceNs;
    }

    // Parse session time-to-live value
    if (isset($xmlCfg->sessionTimeToLive)) {
      $cfg['sessionTtl'] = (int) $xmlCfg->sessionTimeToLive->__toString();
    } else {
      $cfg['sessionTtl'] = SessionManager::DEFAULT_SESSION_TTL;
    }

    // Parse website hostName, required for openId login
    if (isset($xmlCfg->hostName)) {
      $cfg['host'] = $xmlCfg->hostName->__toString();
    } else {
      $cfg['host'] = $_SERVER['SERVER_NAME'];
    }

    return $cfg;
  }
}
