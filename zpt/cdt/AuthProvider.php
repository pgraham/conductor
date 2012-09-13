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
namespace zpt\cdt;

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;
use \zpt\cdt\auth\Authorize;
use \zpt\cdt\auth\SessionManager;
use \zpt\cdt\exception\AuthException;
use \zpt\cdt\model\User;
use \zpt\cdt\model\Visitor;

use \LightOpenId;

/**
 * This class ensures that the requesting user is assigned to a session.  The
 * session can optionally be associated with a user.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class AuthProvider {

  private $_session = null;

  private $_openId;
  private $_visitor;

  public function authenticate($username, $password) {
    $pwHash = md5($password);

    $c = new Criteria();
    $c->addEquals('username', $username);
    $c->addEquals('password', $pwHash);

    $persister = Persister::get('zpt\cdt\model\User');
    $user = $persister->retrieveOne($c);

    return $user;
  }

  /**
   * Check if the given password is the password for the current user. Returns
   * false if there is no current user.
   *
   * @param string $password Password to check.
   * @return boolean
   */
  public function checkPassword($password) {
    $user = $this->getSession()->getUser();
    if ($user === null) {
      throw new AuthException(AuthException::NOT_LOGGED_IN);
    }

    $pwHash = md5($password);
    return $user->getPassword() === $pwHash;
  }

  /**
   * Check if the current session has the requested authorization and if not
   * throw an exception
   *
   * @param string $permName
   * @param string $level Default 'write'
   */
  public function checkPermission($permName, $level = 'write') {
    if (!$this->hasPermission($permName, $level)) {
      throw new AuthException(AuthException::NOT_AUTHORIZED,
        "$permName:$level");
    }
  }

  /**
   * Getter for the current openId status.
   *
   * This will be null if no openId login attempt has been made.
   *
   * @return LightOpenId
   */
  public function getOpenId() {
    return $this->_openId;
  }

  /**
   * Getter for an already initiated session.
   *
   * Do not call this function is a login attempt is being made, use init(...)
   * instead.
   *
   * @return Session
   */
  public function getSession() {
    $this->init();
    return $this->_session;
  }

  /**
   * Getter for the visitor instance associated with this request.
   *
   * TODO Move visitor tracking into a module
   *
   * @return Visitor
   */
  public function getVisitor() {
    $this->init();
    return $this->_visitor;
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
  public function init() {
    $this->_initSession();
    $this->_initVisitor();
  }

  /**
   * Process a login request with the given username and password.
   *
   * @param string $username An optional username to use to authenticate.
   * @param string $password An optional password to use to authenticate.
   */
  public function login($username, $password) {
    // Ensure that a session is set
    $this->init();

    // To save some headaches around ambiguous results, never preserve an
    // already authenticated user.  Otherwise, a failed login attempt could
    // appear to be successful since the session would still be associated
    // with a user.  Another way to consider this is that a login attempt
    // is an implicit logout, whether the login succeeds or not
    if ($this->_session->getUser() !== null) {
      $this->_session = SessionManager::newSession();
    }

    $user = $this->authenticate($username, $password);
    if ($user !== null) {
      $this->_session->setUser($user);

      $persister = Persister::get($this->_session);
      $persister->save($this->_session);
    }
  }

  /**
   * Determine if the current session has the given permissions.
   *
   * @param string $permName The name of a permission resource to check
   * @param string $level Optional level of permission on the given resource
   * @return True if the current session has access, false otherwise.
   */
  public function hasPermission($permName, $level = 'write') {
    $this->init();

    if ($this->_session->getUser() !== null) {
      return Authorize::allowed($this->_session->getUser(), $permName, $level);
    }
    return false;
  }

  /**
   * Process an OpenId login.  This will handle an initial redirect to an
   * openid provider or an assertion from an OP
   *
   * TODO This is broken FIXME
   *
   * @param $identity OpenId identity
   */
  public function openIdLogin($identity, $returnUrl) {
    require_once dirname(__FILE__) . '/../../lightopenid/openid.php';
    $openId = new LightOpenId(Conductor::getHostName());

    if (!$openId->mode) {
      $openId->identity = $identity;

      header('Location: ' . $openId->authUrl());
      exit;

    } else {
      
      if ($openId->validate()) {
        // Ensure a session is set
        $this->init();

        // This is a positive assertion.
        // To save some headaches around ambiguous results, never preserve an
        // already authenticated user.  Otherwise, a failed login attempt could
        // appear to be successful since the session would still be associated
        // with a user.  Another way to consider this is that a login attempt
        // is an implicit logout, whether the login succeeds or not.  However,
        // if a current session is unauthenticated, it will be associated with
        // the now logged in user.
        if ($this->_session->getUser() !== null) {
          $this->_session = SessionManager::newSession();
        }

        // Need to assign a user ID to the session
        $persister = Persister::get('zpt\cdt\model\User');
        $c = new Criteria();
        $c->addEquals('oid_identity', $openId->identity);
        $user = $persister->retrieveOne($c);

        if ($user === null) {
          $user = new User();
          $user->setOpenId($openId->identity);
          $persister->save($user);
        }

        $this->_session->setUser($user);
        $persister = Persister::get($this->_session);
        $persister->save($this->_session);

        // Redirect to the same page if handling a synchronous request so
        // that a refresh doesn't re-authenticate the same user
        $this->_redirectIf();
      } else {
        $this->_session = $this->_getSession();
      }

    }

    $this->_openId = $openId;
  }

  /**
   * Update the password for the current user to the given password.
   *
   * @param string $password
   */
  public function updatePassword($password) {
    $user = $this->getSession()->getUser();
    if ($user === null) {
      // There is no user associated with the current session
      throw new AuthException(AuthException::NOT_LOGGED_IN);
    }

    $pwHash = md5($password);
    $user->setPassword($pwHash);

    $persister = Persister::get($user);
    $persister->save($user);
  }

  /* Initialize the session. */
  private function _initSession() {
    if ($this->_session !== null) {
      return;
    }

    // Initialize session
    $this->_session = SessionManager::loadSession(
      isset($_COOKIE['conductorsessid'])
        ? $_COOKIE['conductorsessid']
        : null
    );
  }

  /* Initialize the visitor. */
  private function _initVisitor() {
    // Initialize visitor
    if ($this->_visitor !== null) {
      return;
    }

    $persister = Persister::get('zpt\cdt\model\Visitor');

    $visitor = null;
    if (isset($_COOKIE['visitor_id'])) {
      $visitorKey = $_COOKIE['visitor_id'];

      $c = new Criteria();
      $c->addEquals('key', $visitorKey);
      
      $visitor = $persister->retrieveOne($c);
      if ($visitor === null) {
        // It is possible under certain circumstances to get here.  An
        // example would be if the visitors table is cleared.  Some failure
        // cases may also result in a cookie getting out of sync with the
        // server.  In this case a new visitor ID should be assigned.
        $visitor = $this->_newVisitor($persister);
      }
    } else {
      $visitor = $this->_newVisitor($persister);
    }

    $this->_visitor = $visitor;

  }

  /* Create a new visitor. */
  private function _newVisitor($persister) {
    $visitorKey = uniqid('visitor_', true);

    $visitor = new Visitor();
    $visitor->setKey($visitorKey);
    $persister->save($visitor);

    $tenYearsFromNow = time() + 315569260;
    $path = _P('/');
    setcookie('visitor_id', $visitorKey, $tenYearsFromNow, $path);

    return $visitor;
  }
}
