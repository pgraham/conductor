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

use \RuntimeException;

/**
 * This class implements a generic object for containing configuration values.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigurationValues {
  
  private $_config;

  public function __construct(array $config) {
    $this->_config = $config;
  }

  public function __call($name, $params) {
    if (substr($name, 0, 3) !== 'get') {
      throw new RuntimeException("Only methods in the form 'getXXX' can be " .
        "called on a ConfigurationValues object");
    }

    $propValue = lcfirst(substr($name, 3));
    if (isset($this->_config[$propValue])) {
      return $this->_config[$propValue];
    }
    return null;
  }
}
