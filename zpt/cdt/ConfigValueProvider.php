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
namespace zpt\cdt;

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;

/**
 * This class retrieves configuration values from the database.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigValueProvider {

  // TODO Inject this.
  private $_persister;

  public function __construct() {
    $this->_persister = Persister::get('conductor\model\ConfigValue');
  }

  /**
   * Retrieve the configuration value with the given name.  In order for this to
   * work the database must be setup to handle configuration values.
   *
   * @param string $name The name of the configuration value to retrieve.
   */
  public function getConfigValue($name) {

    $c = new Criteria();
    $c->addEquals('name', $name);

    $rows = $this->_persister->retrieve($c);
    if (count($rows) === 0) {
      return null;
    }

    $obj = $rows[0];
    return $obj->getValue();
  }
}