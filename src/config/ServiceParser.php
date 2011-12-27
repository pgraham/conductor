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

use \SimpleXMLElement;

use \conductor\Exception;

/**
 * This class parses a conductor configuration XML file's <services> section.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServiceParser {

  /**
   * Parses the services defined in a conductor.cfg.xml file.
   *
   * @param SimpleXMLElement $cfg Object representing the <services> node of the
   *   configuration file.
   * @param string $pathRoot The base path for any relative paths defined in the
   *   configuration.
   * @return array An array of ServiceConfig objects.
   */
  public static function parse(SimpleXMLElement $cfg, $pathRoot) {
    $srvcs = array();

    foreach ($cfg->service AS $srvc) {
      if (!isset($srvc['class'])) {
        continue;
      }

      $srvcs[] = new ServiceConfig((string) $srvc['class']);
    }

    return $srvcs;
  }
}