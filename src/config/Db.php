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

use \PDO;
use \PDOException;
use \SimpleXMLElement;

use \conductor\Exception;

/**
 * This class parses the database configuration of a conductor.cfg.xml file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/config
 */
class Db {

  /**
   * Parse the given SimpleXMLElement for database configuration and use it to
   * create a new PDO connection.
   *
   * @param SimpleXMLElement $cfg The database configuration object
   * @param string $pathRoot The base path for any relative file paths in the
   *                           configuration
   * @return PDO
   */
  public static function parse(SimpleXMLElement $cfg, $pathRoot) {
    // Make sure required values are set
    if (!isset($cfg->username)) {
      throw new Exception('No database username specified');
    }
    $user = (string) $cfg->username;

    if (!isset($cfg->password)) {
      throw new Exception('No database password specified');
    }
    $pass = (string) $cfg->password;

    if (!isset($cfg->schema)) {
      throw new Exception('No database schema specified');
    }
    $schema = (string) $cfg->schema;

    // Extract options values if set or use a default
    $driver = (isset($cfg->driver)) ? (string) $cfg->driver : 'mysql';
    $host   = (isset($cfg->host))   ? (string) $cfg->host   : 'localhost';

    return array(
      'db_driver' => $driver,
      'db_host' => $host,
      'db_schema' => $schema,
      'db_user' => $user,
      'db_pass' => $pass
    );
  }
}
