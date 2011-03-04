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
 * @package conductor/widget
 */
namespace conductor\widget;

use \oboe\form\Button;
use \oboe\form\Div;
use \oboe\form\TextInput;
use \oboe\form\Password;
use \oboe\form\Submit;
use \oboe\item;
use \oboe\Composite;
use \oboe\Form;

/**
 * This class encapsulates a stylable login form.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/widget
 */
class LoginForm extends Composite implements item\Body {

  private $_loginBtn;
  private $_passwordLbl;
  private $_usernameLbl;

  /**
   * Create a new login form.
   *
   * @param string $msg An optional message to display to the user about why
   *   they are seeing a login form.
   */
  public function __construct($msg = null, $async = false) {
    $this->initElement(new Form('login'));
    $this->elm->setClass('cdt-LoginForm');
    if ($async) {
      $this->elm->addClass('async');
    }

    if ($msg !== null) {
      $msgDiv = new Div(null, 'cdt-Error');
      $msgDiv->add($msg);
      $this->elm->add($msgDiv);
    }

    $usernameLbl = new Div(null, 'cdt-Label');
    $usernameLbl->add("Username:");

    $usernameTxt = new TextInput('uname');
    $usernameTxt->setClass('cdt-TextInput');

    $usernameInput = new Div(null, 'cdt-FormInput');
    $usernameInput->add($usernameTxt);

    $username = new Div(null, 'cdt-FormInputContainer');
    $username->add($usernameLbl);
    $username->add($usernameInput);

    $passwordLbl = new Div(null, 'cdt-Label');
    $passwordLbl->add("Password:");

    $passwordTxt = new Password('pw');
    $passwordTxt->setClass('cdt-TextInput');

    $passwordInput = new Div(null, 'cdt-FormInput');
    $passwordInput->add($passwordTxt);

    $password = new Div(null, 'cdt-FormInputContainer');
    $password->add($passwordLbl);
    $password->add($passwordInput);

    if ($async) {
      $loginBtn = new Button('Login');
    } else {
      $loginBtn = new Submit('Login');
    }
    $loginBtn->setClass('cdt-Submit');

    $submit = new Div(null, 'cdt-FormInputContainer');
    $submit->add($loginBtn);

    $this->elm->add($username);
    $this->elm->add($password);
    $this->elm->add($submit);

    // Store elements so that they can be modified
    $this->_loginBtn = $loginBtn;
    $this->_passwordLbl = $passwordLbl;
    $this->_usernameLbl = $usernameLbl;
  }

  /**
   * Set the label for the login button.
   *
   * @param string $label
   */
  public function setLoginLabel($label) {
    $this->_loginBtn->setAttribute('value', $label);
  }

  /**
   * Set the label for the password input box.
   *
   * @param string $label
   */
  public function setPasswordLabel($label) {
    $this->_passwordLbl->removeAll();
    $this->_passwordLbl->add($label);
  }

  /**
   * Set the label for the username input box.
   *
   * @param string $label
   */
  public function setUsernameLabel($label) {
    $this->_usernameLbl->removeAll();
    $this->_usernameLbl->add($label);
  }
}
