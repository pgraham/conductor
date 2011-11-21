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
    if (!isset($xmlCfg->hostName)) {
      throw new Exception('Hostname not specified');
    }
    $cfg['host'] = $xmlCfg->hostName->__toString();

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


    // Parse the website's custom Autoloader
    if (isset($xmlCfg->autoloader)) {
      $autoloader = $xmlCfg->autoloader->__toString();
      if (substr($autoloader, 0, 1) !== '/') {
        $autoloader = $pathRoot . '/'. $autoloader;
      }
      $cfg['autoloader'] = $autoloader;
    }

    // Create an array of model files.  No parsing is actually done until
    // necessary
    if (isset($xmlCfg->models)) {
      $cfg['models'] = Model::parse($xmlCfg->models, $pathRoot);
    }

    // Create an array of pages if defined
    if (isset($xmlCfg->pages)) {
      $cfg['pageCfg'] = Page::parse($xmlCfg->pages, $pathRoot);
    }

    // Set the output directory, if not specified use a temporary directory
    if (isset($xmlCfg->targetPath)) {
      $target = $xmlCfg->targetPath->__toString();

      if (substr($target, 0, 1) != '/') {
        $target = $pathRoot . '/' . $target;
      }
    } else {
      $target = sys_get_temp_dir() . '/conductor';
    }

    // Parse the document root
    if (isset($xmlCfg->documentRoot)) {
      $docRoot = $xmlCfg->documentRoot->__toString();

      if (substr($docRoot, 0, 1) != '/') {
        $docRoot = $pathRoot . '/' . $docRoot;
      }
    } else {
      $docRoot = $pathRoot . '/public_html';
    }

    // Parse the file system path to the web-accessible folder that is writable
    // by the web server
    if (isset($xmlCfg->webWritable)) {
      $webWrite = $xmlCfg->webWritable->__toString();

      if (substr($webWrite, 0, 1) != '/') {
        $webWrite = $pathRoot . '/' . $webWrite;
      }
    } else {
      $webWrite = $docRoot . '/gen';
    }

    // Parse the root of the website relative to the domain on which it is
    // hosted
    if (isset($xmlCfg->webRoot)) {
      $webRoot = $xmlCfg->webRoot->__toString();
    } else {
      $webRoot = '/';
    }

    $pathInfo = new WebSitePathInfo($pathRoot, $webRoot, $docRoot, null, null,
      $target, $webWrite);
    $cfg['pathInfo'] = $pathInfo;

    // Parse session time-to-live value
    if (isset($xmlCfg->sessionTimeToLive)) {
      $cfg['sessionTtl'] = (int) $xmlCfg->sessionTimeToLive->__toString();
    } else {
      $cfg['sessionTtl'] = SessionManager::DEFAULT_SESSION_TTL;
    }

    return $cfg;
  }
}
