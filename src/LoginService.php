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
namespace conductor;

use \LightOpenId;

/**
 * This class provides asynchronous login capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Service(name = LoginService)
 * @CsrfToken conductorsessid
 * @Requires Autoloader.php
 */
class LoginService {

  /**
   * Initiate Conductor.
   */
  public function __construct() {
    Conductor::init();
  }

  public function googleLogin() {
    Auth::openIdLogin('https://www.google.com/accounts/o8/id');
    $openId = Auth::getOpenId();
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
   * @requestType post
   */
  public function login($username, $password) {
    Auth::login($username, $password);

    if (Auth::$session->getUser() === null) {
      return Array('msg' => 'Invalid username or password');
    } else {
      return Array('msg' => null);
    }
  }

  public function logout() {
    setcookie('conductorsessid', '', time() - 3600, '/');
  }
}
