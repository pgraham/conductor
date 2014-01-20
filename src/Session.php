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
namespace zpt\cdt;

/**
 * Session object.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Session
{

    private $authProvider;

    public function init()
    {
      $cdtSessionId = $this->authProvider->getSession()->getId();
      session_start();

      $isCdtSessionIdMatch = !isset($_SESSION['cdtSessionId']) ||
                             $_SESSION['cdtSessionId'] === $cdtSessionId;
      if (!$isCdtSessionIdMatch) {
        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
          $params = session_get_cookie_params();
          setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
          );
        }
        session_destroy();
        session_start();
      }
      $_SESSION['cdtSessionId'] = $cdtSessionId;
    }

    public function get($key) {
      if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
      } else {
        return null;
      }
    }

    public function set($key, $value) {
      $_SESSION[$key] = $value;
    }

    public function setAuthProvider(AuthProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }
}
