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
 * @package conductor
 */
namespace conductor;

use \conductor\auth\Authenticate;
use \conductor\auth\Authorize;
use \conductor\auth\SessionManager;
/**
 * This class ensures that the requesting user is assigned to a session.  The
 * session can optionally be associated with a user.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class Auth {

  public static $session = null;

  /**
   * Authenticate the user.  The process is as follows.  Check if there is
   * a login attempt with the request and if so process it.  If there is no
   * login attempt then check if there is a session id in the request's cookie.
   * If there is then check if the session is valid.  If so, load any
   * permissions associated with the session.  If the session is invalid or
   * there is no session id in the request, then initiate a new session with
   * default permissions.
   */
  public static function init() {
    if (self::$session !== null) {
      return;
    }

    $sessionId = ( isset($_COOKIE['conductorsessid']) )
      ? $_COOKIE['conductorsessid']
      : null;

    $session = ( SessionManager::isValid($sessionId) )
      ? SessionManager::loadSession($sessionId)
      : null;

    $sessionUser = ( $session !== null )
      ? $session->getUser()
      : null;

    if (isset($_POST['uname']) || isset($_POST['pw'])) {
      // This is a login attempt.  If the login attempt is successful this will
      // return a User object for the now Authenticated user.  Otherwise null
      // is returned
      $user = Authenticate::loginAttempt($_POST['uname'], $_POST['pw']);

      // Since this is a new login create a new session
      if ($user !== null) {
        $userId = $user->getId();

        if ($session !== null && $sessionUser === null) {
          $session->setUser($user);
          Clarinet::save($session);
        } else if ($sessionUser !== null && $sessionUser->getId() == $userId) {
          self::$session = $session;
        } else {
          self::$session = SessionManager::newSession($user);
        }

      } else if ($sessionUser === null) {
        self::$session = $session;

      } else {
        self::$session = SessionManager::newSession();
      }

    } else if ($sessionId !== null) {
      // This is a returning visitor, validate their session and if valid
      // check if it is associated with a user.  If the session is associated
      // with a user, then load their permissions.  If the session is no longer
      // valid then generate a new session id
      if (SessionManager::isValid($sessionId)) {
        self::$session = SessionManager::loadSession($sessionId);
      } else {
        self::$session = SessionManager::newSession();
      }

    } else {

      // There is no existing session and the user isn't trying to login so
      // generate a session but don't associate any permissions with it
      self::$session = SessionManager::newSession();
    }
  }

  /**
   * Determine if the current session has the given permissions.
   *
   * @param string $permName The name of a permission resource to check
   * @param string $level Optional level of permission on the given resource
   * @return True if the current session has access, false otherwise.
   */
  public static function hasPermission($permName, $level = 'write') {
    if (self::$session === null) {
      self::init();
    }

    if (self::$session->getUser() !== null) {
      return Authorize::allowed(self::$session->getUser(), $permName, $level);
    }
    return false;
  }
}
