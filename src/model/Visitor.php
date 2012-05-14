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
namespace conductor\model;

/**
 * This class represents a visitor to the web application.  All requests that
 * invoke Auth::getVisitor() will return the same visitor instance for the
 * same browser/devices combination unless the user clears their cookies.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = visitors)
 * @NoCrud
 */
class Visitor {

  private $_id;
  private $_key;

  /**
   * @Id
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column
   */
  public function getKey() {
    return $this->_key;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setKey($key) {
    $this->_key = $key;
  }
}
