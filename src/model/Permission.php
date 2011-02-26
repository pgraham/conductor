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
 * Model class for a permission.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/model
 *
 * @Entity(table = permissions)
 */
class Permission {

  private $_id;
  private $_name;
  private $_users;

  /**
   * @Id
   * @Column(name = id)
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column(name = name)
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * @OneToMany(entity = conductor\model\UserPermission)
   */
  public function getUsers() {
    return $this->_users;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }

  public function setUsers(array $users) {
    $this->_users = $users;
  }
}
