<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
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
namespace zpt\cdt\exception;

use \Exception;

/**
 * Exception for any Authorization or Authentication related exceptions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AuthException extends Exception {

  const NOT_AUTHORIZED = 'auth.notAuthorized';
  const NOT_LOGGED_IN = 'auth.notLoggedIn';

  /**
   * Create a new authorization exception.
   *
   * @param string $msg The exception's message.
   * @param integer|Exception $code Either the exception's code or the causing
   *     exception.
   * @param Exception $previous The exception that is the reason this exception
   *     is being thrown.
   */
  public function __construct($code) {
    parent::__construct(call_user_func_array('_L', func_get_args()));
  }
}
