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
use \conductor\model\Session;
use \conductor\model\User;

/**
 * This class manages session objects.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class SessionManager {

  const DEFAULT_SESSION_TTL = 1209600; //60 * 60 * 24 * 14 -- 14 days in seconds

  private static $keyPrefixChars = "abcdefghijklmnopqrstuvwxyz0123456789";

  /**
   * Checks if the session with the given key is valid.
   *
   * @param string $sessionKey
   * @return boolean
   */
  public static function isValid($sessionKey) {
    if ($sessionKey === null) {
      return false;
    }

    $c = new Criteria();
    $c->addEquals('sess_key', $sessionKey);

    $session = Clarinet::getOne('conductor\model\Session', $c);
    if ($session === null) {
      return false;
    }

    $ttl = Conductor::$config['sessionTtl'];
    if (time() - $session->getLastAccess() > $ttl) {
      return false;
    }

    return true;
  }

  /**
   * Loads the session with the given session key.
   *
   * @param string $sessionKey
   * @return conductor\model\Session|null Return the session with the given key
   *   or null if none.
   */
  public static function loadSession($sessionKey) {
    $c = new Criteria();
    $c->addEquals('sess_key', $sessionKey);

    $session = Clarinet::getOne('conductor\model\Session', $c);
    $session->setLastAccess(time());
    Clarinet::save($session);

    return $session;
  }

  /**
   * Initialize a new session a return its instance.
   *
   * @param conductor\model\User $user The user with which to associate the
   *   session
   * @return new session instance
   */
  public static function newSession(User $user = null) {
    $session = new Session();   
    if ($user !== null) {
      $session->setUser($user);
    }

    $prefix = '';
    for ($i = 0; $i < 5; $i++) {
      $char = self::$keyPrefixChars[mt_rand(0, 35)];
      if (mt_rand(0, 1)) {
        $char = strtoupper($char);
      }
      $prefix .= $char;
    }
    $key = uniqid($prefix, true);
    $session->setKey($key);

    Clarinet::save($session);

    // Send the session key to the client
    setcookie('conductorsessid', $session->getKey(), 0, '/');

    return $session;
  }
}
