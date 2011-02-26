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
use \conductor\model\User;

/**
 * This class provides authorization capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class Authorize {

  private static $_lvls = Array
  (
    'read'  => 1,
    'write' => 2
  );
  
  /**
   * Determines a wether or not a user has sufficient permissions to perform an
   * action on a resource.
   *
   * @param conductor\model\User $user The user whose permission are to be
   *   checked.  If null false is returned.
   * @param string $permName The name of the resource on which the action will
   *   be performed.
   * @param string $level The level of access required to perform the action.
   * @return boolean
   */
  public static function allowed(User $user, $permName, $level) {
    if ($user === null) {
      return false;
    }

    $requestedLvl = self::$_lvls[$level];

    $userPerms = $user->getPermissions();
    foreach ($userPerms AS $perm) {
      if ($perm->getPermissionName() == $permName) {
        $userLvl = self::$_lvls[$perm->getLevel()];
        return $userLvl >= $requestedLvl;
      }
    }
    return false;
  }
}
