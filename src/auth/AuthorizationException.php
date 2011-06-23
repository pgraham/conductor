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
 * @package conductor/auth
 */
namespace conductor\auth;

/**
 * Clarinet exception class.  All messages are prepended with 'Clarinet: '.
 * The constructor is also overloaded to allow a previous exception to be
 * given without requiring a code.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/auth
 */
class AuthorizationException extends \Exception {

  /* Used by PageLoader::loadPage to customize the returned login form */
  private $_usernameLbl;

  /* Used by PageLoader::loadPage to customize the returned login form */
  private $_passwordLbl;

  /** Array of additional infomation to add to the bottom of the login form */
  private $_content = array();

  /**
   * Create a new authorization exception.  Throwing an authorization exception
   * as a result of calling PageLoader::loadPage() will result in the a login
   * form being displayed.  For this reason it is possible to set customization
   * information for the login form through this exception.
   *
   * @param string $msg The exception's message.
   * @param integer|Exception $code Either the exception's code or the causing
   *     exception.
   * @param Exception $previous The exception that is the reason this exception
   *     is being thrown.
   */
  public function __construct($msg = null) {
    parent::__construct($msg);
  }

  public function add($ctnt) {
    $this->_content[] = $ctnt;
  }

  public function getContent() {
    $this->_content;
  }

  public function getPasswordLabel() {
    return $this->_passwordLbl;
  }

  public function getUsernameLabel() {
    return $this->_usernameLbl;
  }

  public function setPasswordLabel($passwordLbl) {
    $this->_passwordLbl = $passwordLbl;
  }

  public function setUsernameLabel($usernameLbl) {
    $this->_usernameLbl = $usernameLbl;
  }
}
