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

/**
 * This class provides asynchronous login capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Service( name = ConductorService )
 * @CsrfToken conductorsessid
 * @Requires Autoloader.php
 */
class Service {

  /**
   * Initiate the service.  This ensures that conductor has been initialized.
   */
  public function __construct() {
    Conductor::init();
  }

  /**
   * @returnType html
   */
  public function loadPage($pageId) {
    $elm = PageLoader::loadPage($pageId, true);
    return $elm->__toString();
  }

  /**
   * @requestType post
   */
  public function login($username, $password) {
    Auth::init($username, $password);

    if (Auth::$session->getUser() === null) {
      return Array('msg' => 'Invalid username or password');
    } else {
      return Array('msg' => null);
    }
  }
}
