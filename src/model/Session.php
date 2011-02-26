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
 * @package conductor/model
 */
namespace conductor\model;

/**
 * This class represents a session in a web application.  All requests that
 * initiate the Auth model get associated with a session.  If the session is
 * associated with a user it will recieve any elevated privileges owned by the
 * user.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/model
 *
 * @Entity(table = session)
 */
class Session {

  private $_id;
  private $_key;
  private $_user;
  private $_created;
  private $_lastAccess;

  public function __construct() {
    $this->_created = time();
    $this->_lastAccess = $this->_created;
  }

  /**
   * @Id
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column(name = sess_key)
   */
  public function getKey() {
    return $this->_key;
  }

  /**
   * @Column
   */
  public function getCreated() {
    return $this->_created;
  }

  /**
   * @ManyToOne(entity = conductor\model\User)
   */
  public function getUser() {
    return $this->_user;
  }

  /**
   * @Column(name = last_access)
   */
  public function getLastAccess() {
    return $this->_lastAccess;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setKey($key) {
    $this->_key = $key;
  }

  public function setCreated($created) {
    $this->_created = $created;
  }

  public function setUser(User $user) {
    $this->_user = $user;
  }

  public function setLastAccess($lastAccess) {
    $this->_lastAccess = $lastAccess;
  }
}
