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
namespace zpt\cdt\model;

use \zpt\cdt\model\Permission;
use \zpt\cdt\model\User;

/**
 * This class represents a link between a user and a permission and attaches an
 * access level to the relationship.  The relationship is many to many.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = users_permissions_link)
 * @NoCrud
 */
class UserPermission {

  private $_id;
  private $_user;
  private $_permission;
  private $_lvl;

  /**
   * @Id
   * @Column(name = id)
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @ManyToOne(entity = zpt\cdt\model\User)
   */
  public function getUser() {
    return $this->_user;
  }

  /**
   * @ManyToOne(entity = zpt\cdt\model\Permission)
   */
  public function getPermission() {
    return $this->_permission;
  }

  /**
   * @Column(name = level)
   * @Enumerated(values = { read, write })
   */
  public function getLevel() {
    return $this->_lvl;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setUser(User $user) {
    $this->_user = $user;
  }

  public function setPermission(Permission $permission) {
    $this->_permission = $permission;
  }

  public function setLevel($lvl) {
    $this->_lvl = $lvl;
  }

  /**
   * Helper function for getting the linked permission name.
   *
   * @return string
   */
  public function getPermissionName() {
    if ($this->_permission === null) {
      return null;
    }
    return $this->_permission->getName();
  }
}
