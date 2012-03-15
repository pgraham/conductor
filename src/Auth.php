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
use \conductor\auth\AuthService;
use \conductor\auth\SessionManager;
use \conductor\model\User;
use \conductor\model\Visitor;
use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;

use \LightOpenId;

/**
 * This class ensures that the requesting user is assigned to a session.  The
 * session can optionally be associated with a user.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class Auth {

  /**
   * @deprecated Use getSession instead.
   */
  public static $session = null;

  private static $_openId;
  private static $_visitor;

  /**
   * Getter for the current openId status.
   *
   * This will be null if no openId login attempt has been made.
   *
   * @return LightOpenId
   */
  public static function getOpenId() {
    return self::$_openId;
  }

  /**
   * Getter for an already initiated session.
   *
   * Do not call this function is a login attempt is being made, use init(...)
   * instead.
   *
   * @return Session
   */
  public static function getSession() {
    self::init();
    return self::$session;
  }

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
        
        $self::$_visitor = $persister->retrieveOne($c);
      } else {
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
        self::$_visitor = $visitor;
      }

    }
    return self::$_visitor;
  }

  /**
   * Authenticate the user.  The process is as follows.  Check if there is
   * a login attempt with the request and if so process it.  If there is no
   * login attempt check if there is opendId authentication info in the request
   * and process it. If there is no login attempt or openid info then check if
   * there is a session id in the request's cookie. If there is then check if
   * the session is valid.  If so, load any permissions associated with the
   * session. If the session is invalid or there is no session id in the
   * request, then initiate a new session with default permissions.
   */
  public static function init() {
    // Only authenticate once per request
    if (self::$session !== null) {
      return;
    }

    // If crendentials have been provided, attempt to authenticate with them
    if (isset($_POST['uname']) && isset($_POST['pw'])) {
      // A successful login attempt for a synchronous request will result
      // in a page redirect back to the same page in order to avoid
      // re-logging in if the user refreshes the page.
      $session = self::login($_POST['uname'], $_POST['pw']);

    } else if (isset($_GET['openid_identity'])) {
      $session = self::openIdLogin($_GET['openid_identity']);

    } else {
      $session = self::_getSession();
    }

    self::$session = $session;
  }

  /**
   * Process a login request with the given username and password.
   *
   * @param string $username An optional username to use to authenticate.
   * @param string $password An optional password to use to authenticate.
   */
  public static function login($username, $password) {
    // To save some headaches around ambiguous results, never preserve an
    // already authenticated user.  Otherwise, a failed login attempt could
    // appear to be successful since the session would still be associated
    // with a user.  Another way to consider this is that a login attempt
    // is an implicit logout, whether the login succeeds or not
    $session = self::_getSession(SessionManager::NEW_IF_AUTHENTICATED);

    $user = Authenticate::login($username, $password);
    if ($user !== null) {
      $session->setUser($user);

      $persister = Persister::get($session);
      $persister->save($session);

      // If this is a synchronous request, redirect to the current page so
      // that a reload doesn't resubmit the login credentials
      self::_redirectIf();
    }
    return $session;
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

  /**
   * Process an OpenId login.  This will handle an initial redirect to an
   * openid provider or an assertion from an OP
   *
   * @param $identity OpenId identity
   */
  public static function openIdLogin($identity, $returnUrl = null) {
    require_once dirname(__FILE__) . '/../../lightopenid/openid.php';
    $openId = new LightOpenId(Conductor::getHostName());


    if (!$openId->mode) {
      $openId->identity = $identity;

      if ($returnUrl !== null) {
        $openId->returnUrl = $returnUrl;
      }

      // If this is a synchronous request, redirect to the current page so
      // that a reload doesn't resubmit the login credentials
      self::_redirectIf($openId->authUrl());

      // Don't create a session until now so that a session isn't created for
      // just this request since it will only be used for this one request.
      $session = self::_getSession();

    } else {
      
      if ($openId->validate()) {

        // This is a positive assertion.
        // To save some headaches around ambiguous results, never preserve an
        // already authenticated user.  Otherwise, a failed login attempt could
        // appear to be successful since the session would still be associated
        // with a user.  Another way to consider this is that a login attempt
        // is an implicit logout, whether the login succeeds or not.  However,
        // if a current session is unauthenticated, it will be associated with
        // the now logged in user.
        $session = self::_getSession(SessionManager::NEW_IF_AUTHENTICATED);

        // Need to assign a user ID to the session
        $persister = Persister::get('conductor\model\User');
        $c = new Criteria();
        $c->addEquals('oid_identity', $openId->identity);
        $user = $persister->retrieveOne($c);

        if ($user === null) {
          $user = new User();
          $user->setOpenId($openId->identity);
          $persister->save($user);
        }

        $session->setUser($user);
        $persister = Persister::get($session);
        $persister->save($session);

        // Redirect to the same page if handling a synchronous request so
        // that a refresh doesn't re-authenticate the same user
        self::_redirectIf();
      } else {
        $session = self::_getSession();
      }

    }

    self::$_openId = $openId;
    return $session;
  }

  private static function _getSession($newIfAuthenticated = false) {
    $sessId = isset($_COOKIE['conductorsessid'])
      ? $_COOKIE['conductorsessid']
      : null;

    return SessionManager::loadSession($sessId, $newIfAuthenticated);
  }

  /*
   * Redirect to the given URL (or REQUEST_URI if not provided) if the current
   * request is not asynchronous.
   */
  private static function _redirectIf($url = null) {
    $asyncRequest = false;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
      $requestType = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
      if ($requestType == 'xmlhttprequest') {
        $asyncRequest = true;
      }
    }
    if (!$asyncRequest) {
      if ($url === null) {
        $url = $_SERVER['REQUEST_URI'];
      }

      header("Location: $url");
      exit;
    }
  }
}
