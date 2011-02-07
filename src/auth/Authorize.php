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
 * @package conductor/auth
 */
namespace conductor\auth;

use \clarinet\Clarinet;
use \clarinet\Criteria;

/**
 * This class provides authorization capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class Authorize {
  
  /**
   * This class loads the permissions for a given user.  This class can be
   * eliminated once Clarinet supports foreign keys and the Auth class has been
   * updated to use them.
   *
   * @deprecated
   * @param integer $userId The id of the user whose permission to load
   * @return array An array of Permission model objects.
   */
  public static function loadPermissions($userId) {
    $c = new Criteria();
    $c->addEquals('user_id', $userId);

    $perms = Clarinet::get('conductor\model\UserPermLink', $c);
  }
}
