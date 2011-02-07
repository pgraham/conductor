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
 * This class represents a link between a user and permission.  The relationship
 * is many to many.  This class can be eliminated once Clarinet supports foreign
 * keys.
 *
 * @deprecated
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/model
 *
 * @Entity(table = users_permissions_link)
 */
class UserPermLink {

  private $_id;
  private $_userId;
  private $_permId;
  private $_lvl;

  /**
   * @Id
   * @Column(name = id)
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column(name = user_id)
   */
  public function getUserId() {
    return $this->_userId;
  }

  /**
   * @Column(name = permission_id)
   */
  public function getPermissionId() {
    return $this->_permId;
  }

  /**
   * @Column(name = level)
   * @Enumerated(values = { read, write }
   */
  public function getLevel() {
    return $this->_lvl;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setUserId($userId) {
    $this->_userId = $userId;
  }

  public function setPermId($permId) {
    $this->_permId = $permId;
  }

  public function setLevel($lvl) {
    $this->_lvl = $lvl;
  }
}
