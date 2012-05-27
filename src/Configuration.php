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

use \Exception;
use \ArrayObject;

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
    $cfg = array();

    $xmlCfg = simplexml_load_file($configPath, 'SimpleXMLElement',
      LIBXML_NOCDATA);

    if (isset($xmlCfg->namespace)) {
      $cfg['namespace'] = (string) $xmlCfg->namespace;
    }

    // Parse database configuration
    // ----------------------------
    if (!isset($xmlCfg->db)) {
      throw new Exception('No database configuration found');
    }
    $dbConfig = array();

    if (!isset($xmlCfg->db->username)) {
      throw new Exception('No database username specified');
    }
    $dbConfig['db_user'] = (string) $xmlCfg->db->username;

    if (!isset($xmlCfg->db->password)) {
      throw new Exception('No database password specified');
    }
    $dbConfig['db_pass'] = (string) $xmlCfg->db->password;

    if (!isset($xmlCfg->db->schema)) {
      throw new Exception('No database schema specified');
    }
    $dbConfig['db_schema'] = (string) $xmlCfg->db->schema;

    $dbConfig['db_driver'] = (isset($xmlCfg->db->driver))
      ? (string) $xmlCfg->db->driver
      : 'mysql';

    $dbConfig['db_host'] = (isset($xmlCfg->db->host))
      ? (string) $xmlCfg->db->host
      : 'localhost';

    $cfg['db_config'] = $dbConfig;

    // 
    // Build path info
    // ---------------

    // Website config is found at the root of the website
    $root = realpath(dirname($configPath));
    $webRoot = isset($xmlCfg->webRoot)
      ? (string) $xmlCfg->webRoot
      : '/';
    $docRoot = "$root/htdocs";
    $lib = "$root/lib";
    $src = "$root/src";
    $target = "$root/target";

    $pathInfo = new ArrayObject(array(
      'root' => $root,
      'webRoot' => $webRoot,
      'docRoot' => $docRoot,
      'lib' => $lib,
      'src' => $src,
      'target' => $target,
    ));

    // Add a closure for appending the web root to absolute web paths if
    // necessary
    if ($webRoot === '/') {
      $pathInfo->asWebPath = function ($path) {
        return $path;
      };
    } else {
      $pathInfo->asWebPath = function ($path) use ($webRoot) {
        return $webRoot . $path;
      };
    }

    // Add a closure to strip the web root from web paths if necessary
    if ($webRoot === '/') {
      $pathInfo->asAbsWebPath = function ($path) {
        return $path;
      };
    } else {
      $pathInfo->asAbsWebPath = function ($path) use ($webRoot) {
        if (strpos($path, $webRoot) === 0) {
          $path = substr($path, strlen($webRoot));
        }
        return $path;
      };
    }

    // FIXME - Once it is possible to invoke functions assigned as object
    // properties, e.g. $pathInfo->asWebPath(...), make these globals go away.
    global $asWebPath, $asAbsWebPath;
    $asWebPath = $pathInfo->asWebPath;
    $asAbsWebPath = $pathInfo->asAbsWebPath;

    $cfg['pathInfo'] = $pathInfo;

    $cfg['devMode'] = is_writeable($target);

    return $cfg;
  }

}
