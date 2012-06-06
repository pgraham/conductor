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
      $response->setData($this->login($data['uname'], $data['pw']));
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

  public function login($username, $password) {
    $this->_authProvider->login($username, $password);

    if ($this->_authProvider->getSession->getUser() === null) {
      return array(
        'success' => false,
        'msg' => 'Invalid username or password'
      );
    } else {
      return array(
        'success' => true,
        'msg' => null
      );
    }
  }

  public function logout() {
    setcookie('conductorsessid', '', time() - 3600, '/');
    return array('success' => true);
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }
}
