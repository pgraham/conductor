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

/**
 * This class encapsulates configuration about a remote service class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServiceConfig {

  /* The name of the class that defines the service. */
  private $_className;

  /**
   * Create a new ServiceConfig object for the service of the given class.
   *
   * @param string $className
   */
  public function __construct($className) {
    $this->_className = $className;
  }

  /**
   * Getter for the name of the service class represented by this object.
   *
   * @return string
   */
  public function getClassName() {
    return $this->_className;
  }

}
