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
 */
namespace zpt\cdt\srvc;

use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \LightOpenId;

/**
 * This class provides asynchronous login capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Uri /login
 * @Uri /logout
 */
class LoginService extends BaseRequestHandler implements RequestHandler {

  /** @Injected */
  private $_authProvider;

  public function post(Request $request, Response $response) {
    $uri = $request->getUri();

    $data = $request->getData();
    if ($uri === '/login') {
      $this->login($request, $response);
    } else if ($uri === '/logout') {
      $response->setData($this->logout());
    }
  }

  // TODO - This needs to be handled by the service
  public function googleLogin() {
    $this->_authProvider->openIdLogin('https://www.google.com/accounts/o8/id');

    $openId = $this->_authProvider->getOpenId();
    if (!$openId->mode) {
      $openId->identity = 'https://www.google.com/accounts/o8/id';
      $openId->returnUrl = 'http://zeptech.ca/5x5calc/';

      return array(
        'auth_status' => 'redirect',
        'msg' => 'please provide your credentials',
        'url' => $openId->authUrl()
      );

    } else if ($openId->mode == 'cancel') {
      return array(
        'auth_status' => 'cancelled',
        'msg' => 'User has cancelled authentication'
      );
    } else if ($openId->validate()) {
      return array(
        'auth_status' => 'authenticated',
        'msg' => 'User has authenticated'
      );
    } else {
      return array(
        'auth_status' => 'failed',
        'msg' => 'User has not authenticated'
      );
    }
  }

  /*
   * TODO Handle openIdLogins
   *
   * if (isset($_GET['openid_identity'])) {
   *   $session = $this->openIdLogin($_GET['openid_identity']);
   * }
   */
  public function login($request, $response) {
    $data = $request->getData();

    if (isset($data['uname']) && isset($data['pw'])) {
      $username = $data['uname'];
      $password = $data['pw'];
      $this->_authProvider->login($username, $password);
    }

    $this->_redirectIf();

    // If not redirected then this is an AJAX request so return a JSON
    // response
    if ($this->_authProvider->getSession->getUser() === null) {
      $response->setData(array(
        'success' => false,
        'msg' => 'Invalid username or password'
      ));
    } else {
      $response->setData(array(
        'success' => true,
        'msg' => null
      ));
    }
  }

  public function logout() {
    setcookie('conductorsessid', '', time() - 3600, '/');
    return array('success' => true);
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }

  /*
   * Redirect to the given URL (or HTTP_REFERER if not provided) if the current
   * request is not asynchronous.
   */
  private function _redirectIf($url = null) {
    global $asWebPath;

    $asyncRequest = false;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
      $requestType = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
      if ($requestType == 'xmlhttprequest') {
        $asyncRequest = true;
      }
    }
    if (!$asyncRequest) {
      if ($url === null) {
        if (isset($_SERVER['HTTP_REFERER'])) {
          $url = $_SERVER['HTTP_REFERER'];
        } else {
          $url = $asWebPath('/');
        }
      }

      header("Location: $url");
      exit;
    }
  }
}
