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
 * @Service
 */
class LoginService {

  /** @Injected */
  private $_authProvider;

  /**
   * TODO L10N messages
   *
   * @Method post
   * @Uri /oauth
   */
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

  /**
   * @Method post
   * @Uri /login
   */
  public function login(Request $request, Response $response) {
    $data = $request->getData();

    if (isset($data['uname']) && isset($data['pw'])) {
      $username = $data['uname'];
      $password = $data['pw'];
      $this->_authProvider->login($username, $password);
    }

    if ($this->isAsyncRequest()) {
      if ($this->_authProvider->getSession()->getUser() === null) {
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
    } else {
      $this->redirectToReferer();
    }
  }

  /**
   * @Method post
   * @Uri /logout
   */
  public function logout(Request $request, Response $response) {
    $this->_authProvider->logout();

    if ($this->isAsyncRequest()) {
      $response->setData(array('success' => true));
    } else {
      $this->redirectToReferer();
    }
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }

  /* Determine if the current request is asynchronous. */
  private function isAsyncRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  /* Sets a Location header to redirect the browser to the referring page. */
  private function redirectToReferer() {
    $this->redirect($_SERVER['HTTP_REFERER'] ?: _P('/'));
  }

  /* Sets a Location header to redirect the browser to the given URL. */
  private function redirect($url) {
    header("Location: $url");
    exit;
  }

}
