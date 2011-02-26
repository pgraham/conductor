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
 * Model class for a user.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/model
 *
 * @Entity(table = users)
 */
class User {

  private $_id;
  private $_username;
  private $_password;

  private $_permissions;

  /**
   * @Id
   * @Column(name = id)
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column(name = username)
   */
  public function getUsername() {
    return $this->_username;
  }

  /**
   * @Column(name = password)
   */
  public function getPassword() {
    return $this->_password;
  }

  /**
   * @OneToMany(entity = conductor\model\UserPermission)
   */
  public function getPermissions() {
    return $this->_permissions;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setUsername($username) {
    $this->_username = $username;
  }

  public function setPassword($password) {
    $this->_password = $password;
  }

  public function setPermissions(Array $permissions) {
    $this->_permissions = $permissions;
  }
}