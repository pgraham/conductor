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
use \conductor\Conductor;
use \conductor\Exception;

/**
 * This class provides authentication capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class Authenticate {

  /**
   * Attempt to login.
   *
   * @param string $username The username
   * @param string $password The password
   * @return User | null If the login is successful a user object is returned.
   */
  public static function login($username, $password) {
    $pwHash = md5($password);

    $c = new Criteria();
    $c->addEquals('username', $username);
    $c->addEquals('password', $pwHash);
    $user = Clarinet::getOne('conductor\model\User', $c);

    return $user;
  }
}
