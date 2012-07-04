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

use \conductor\model\Session;
use \conductor\model\User;
use \conductor\Conductor;
use \zeptech\orm\runtime\Clarinet;
use \zeptech\orm\runtime\Criteria;

/**
 * This class manages session objects.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class SessionManager {

  /**
   * The default amount of time for which an inactive session remains valid.
   */
  const DEFAULT_SESSION_TTL = 3600; // 60 * 60 = 1 hour in seconds

  /**
   * Constant which when passed to loadSession will cause a loaded session to
   * be discarded if it has already been authenticated.  This is used when an
   * authentication attempt is successful in order to reuse an existing,
   * unauthenticated session.
   */
  const NEW_IF_AUTHENTICATED = true;

  /* Characters from which a session id prefix will be randomly generated */
  private static $keyPrefixChars = "abcdefghijklmnopqrstuvwxyz0123456789";

  /**
   * Loads the session with the given session key if it exists.  If the session
   * does not exist or the session is expired then a new session is returned.
   *
   * @param string $sessionKey
   * @return conductor\model\Session|null Return the session with the given key
   *   or null the session has expired or does not exist.
   */
  public static function loadSession($sessionKey, $newIfAuthenticated = false) {
    if ($sessionKey === null) {
      return self::newSession();
    }

    $c = new Criteria();
    $c->addEquals('sess_key', $sessionKey);

    $session = Clarinet::getOne('conductor\model\Session', $c);
    if ($session === null) {
      return self::newSession();
    }

    if ($session->isExpired(self::DEFAULT_SESSION_TTL)) {
      return self::newSession();
    }

    if ($newIfAuthenticated && $session->getUser() !== null) {
      return self::newSession();
    }

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
    global $asWebPath;
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
    $path = $asWebPath('/');
    setcookie('conductorsessid', $session->getKey(), 0, $path);

    return $session;
  }
}
