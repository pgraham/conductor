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
 * @package conductor/config
 */
namespace conductor\config;

/**
 * This class parses a conductor configuration XML file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/config
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

    $xmlCfg = simplexml_load_file($configPath, 'SimpleXMLElement',
      LIBXML_NOCDATA);

    // Any paths defined in the configuration file will be evaluated as relative
    // to the file.  This path is used to determine namespaces while scanning
    // model files so we need to resolve its 'real' path.
    $pathRoot = realpath(dirname($configPath));

    // Set the web site's title
    if (isset($xmlCfg->title)) {
      $cfg['title'] = $xmlCfg->title->__toString();
    } else {
      $cfg['title'] = 'Powered by Conductor';
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
    $cfg['target'] = $target;

    // Create a connection to the database
    if (!isset($xmlCfg->db)) {
      throw new Exception('No database configuration found');
    }
    $cfg['pdo'] = Db::parse($xmlCfg->db, $pathRoot);

    // Create an array of model files.  No parsing is actually done until
    // necessary
    if (isset($xmlCfg->models)) {
      $cfg['models'] = Model::parse($xmlCfg->models, $pathRoot);
    }

    // Create an array of pages if defined
    if (isset($xmlCfg->pages)) {
      $cfg['pageCfg'] = Page::parse($xmlCfg->pages, $pathRoot);
    }

    return $cfg;
  }
}
