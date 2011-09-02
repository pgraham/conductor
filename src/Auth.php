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

use \clarinet\Clarinet;
use \clarinet\Criteria;
use \clarinet\Persister;

use \conductor\auth\Authenticate;
use \conductor\auth\Authorize;
use \conductor\auth\AuthService;
use \conductor\auth\SessionManager;
use \conductor\model\Visitor;

/**
 * This class ensures that the requesting user is assigned to a session.  The
 * session can optionally be associated with a user.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class Auth {

  public static $session = null;

  private static $_visitor;

  /**
   * Getter for the visitor instance associated with this request.
   *
   * @return Visitor
   */
  public static function getVisitor() {
    if (self::$_visitor === null) {
      $persister = Persister::get('conductor\model\Visitor');

      $visitor = null;
      if (isset($_COOKIE['visitor_id'])) {
        $visitorKey = $_COOKIE['visitor_id'];

        $c = new Criteria();
        $c->addEquals('key', $visitorKey);
        
        $visitor = $persister->retrieveOne($c);
      }

      if ($visitor === null) {
        $visitorKey = uniqid('visitor_', true);

        $visitor = new Visitor();
        $visitor->setKey($visitorKey);
        $persister->save($visitor);

        // TODO - Support IP addresses
        $domainParts = explode('.', $_SERVER['SERVER_NAME']);
        if (count($domainParts) > 2) {
          $domainParts = array_slice($domainParts, -2);
        }
        $domain = implode('.', $domainParts);

        setcookie('visitor_id', $visitorKey, 0, '/', ".$domain");
      }

      self::$_visitor = $visitor;
    }

    return self::$_visitor;
  }

  /**
   * Authenticate the user.  The process is as follows.  Check if there is
   * a login attempt with the request and if so process it.  If there is no
   * login attempt then check if there is a session id in the request's cookie.
   * If there is then check if the session is valid.  If so, load any
   * permissions associated with the session.  If the session is invalid or
   * there is no session id in the request, then initiate a new session with
   * default permissions.
   *
   * @param string $username An optional username to use to authenticate.
   * @param string $password An optional password to use to authenticate.
   */
  public static function init($username = null, $password = null) {
    // Only authenticate once per request
    if (self::$session !== null) {
      return;
    }

    // If crendentials have been provided, attempt to authenticate with them
    if ($username !== null && $password !== null) {
      // To save some headaches around ambiguous results, never preserve an
      // already authenticated user.  Otherwise, a failed login attempt could
      // appear to be successful since the session would still be associated
      // with a user.  Another way to consider this is that a login attempt
      // is an implicit logout, whether the login succeeds or not
      $session = SessionManager::newSession();

      $user = Authenticate::login($username, $password);
      if ($user !== null) {
        $session->setUser($user);

        $persister = Persister::get($session);
        $persister->save($session);

        // If this is a synchronous request, redirect to the current page so
        // that a reload doesn't resubmit the login credentials
        $asyncRequest = false;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
          $requestType = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
          if ($requestType == 'xmlhttprequest') {
            $asyncRequest = true;
          }
        }
        if (!$asyncRequest) {
          header('Location: ' . $_SERVER['PHP_SELF']);
          exit;
        }
      }
    } else {
      // The following ternary will always result in the variable being assigned
      // something.  This is because the loadSession method will return a new
      // session if the loaded session is invalid
      $session = isset($_COOKIE['conductorsessid'])
        ? SessionManager::loadSession($_COOKIE['conductorsessid'])
        : SessionManager::newSession();
    }

    self::$session = $session;
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
