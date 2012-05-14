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
 * This class represents a session in a web application.  All requests that
 * initiate the Auth model get associated with a session.  If the session is
 * associated with a user it will recieve any elevated privileges owned by the
 * user.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = session)
 * @NoCrud
 */
class Session {

  private $_id;
  private $_key;
  private $_user;
  private $_lastAccess;

  public function __construct() {
    $this->_lastAccess = time();
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
   * @Column(name = last_access)
   */
  public function getLastAccess() {
    return $this->_lastAccess;
  }

  /**
   * @ManyToOne(entity = conductor\model\User)
   */
  public function getUser() {
    return $this->_user;
  }

  /**
   * Transient method that determines if the session represented by this object
   * has expired.  A session has expired if it has not been accessed in the
   * configured timeout period.
   *
   * @param integer $ttl The time, in seconds, that a session remains valid
   *   after its most recent access.
   * @return boolean
   */
  public function isExpired($ttl) {
    return time() - $this->getLastAccess() > $ttl;
  }


  public function setId($id) {
    $this->_id = $id;
  }

  public function setKey($key) {
    $this->_key = $key;
  }

  public function setLastAccess($lastAccess) {
    $this->_lastAccess = $lastAccess;
  }

  public function setUser(User $user) {
    $this->_user = $user;
  }
}
