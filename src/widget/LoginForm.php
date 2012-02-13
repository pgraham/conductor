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

use \oboe\attr\CanSubmit;
use \oboe\Composite;
use \oboe\Element;

/**
 * This class encapsulates a stylable login form.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/widget
 */
class LoginForm extends Composite {

  /**
   * Constant to use with constructor to specify that the form should be
   * submitted asynchronously.
   */
  const ASYNC = true;

  /* The label for the login btn.  Default: 'Login' */
  private $_loginBtn;

  /* The label for the password box.  Default: 'Password:' */
  private $_passwordLbl;

  /* The label for the username box.  Default: 'Username:' */
  private $_usernameLbl;

  /**
   * Create a new login form.
   *
   * @param string $caption An optional message to display to the user about why
   *   they are seeing a login form.
   * @param boolean $async Whether or not to perform the login (submit the form)
   *   asynchronously.  Default: false.  To set to true use the ASYNC_SUBMIT
   *   constant, i.e. $form = new LoginForm("LOGIN PLEASE", LoginForm::ASYNC);
   */
  public function __construct($caption = null, $async = false) {
    $this->initElement(
      Element::form()
        ->setId('login')
        ->setMethod(CanSubmit::METHOD_POST)
        ->setClass('cdt-LoginForm')
    );

    if ($caption !== null) {
      $this->elm->add(
        Element::div()
          ->addClass('cdt-Caption')
          ->add($caption)
      );
    }

    $usernameLbl = Element::div()
      ->addClass('cdt-Label')
      ->add("Username:");
    $this->elm->add(
      Element::div()
        ->addClass('cdt-FormInputContainer')
        ->add($usernameLbl)
        ->add(
          Element::div()
            ->addClass('cdt-FormInput')
            ->add(
              Element::textInput('uname')
                ->setClass('cdt-TextInput')
            )
        )
    );

    $passwordLbl = Element::div()
      ->addClass('cdt-Label')
      ->add("Password:");
    $this->elm->add(
      Element::div()
        ->addClass('cdt-FormInputContainer')
        ->add($passwordLbl)
        ->add(
          Element::div()
            ->addClass('cdt-FormInput')
            ->add(
              Element::password('pw')
                ->setClass('cdt-TextInput')
            )
        )
    );

    if ($async) {
      $loginBtn = Element::button('Login');
    } else {
      $loginBtn = Element::submit('Login');
    }
    $loginBtn->addClass('cdt-Submit');

    $this->elm->add(
      Element::div()
        ->addClass('cdt-FormSubmitContainer')
        ->add($loginBtn)
    );

    // If form will be submitted asynchronously add a placeholder div for error
    // messages.
    if ($async) {
      $this->elm->add(Element::div()->addClass('cdt-Error'));
    }

    // Store elements so that they can be modified
    $this->_loginBtn = $loginBtn;
    $this->_passwordLbl = $passwordLbl;
    $this->_usernameLbl = $usernameLbl;
  }

  /**
   * Add content to the bottom of the form.
   *
   * @param mixed $ctnt
   */
  public function add($ctnt) {
    $this->elm->add($ctnt);
  }

  /**
   * Add the login form and it's resources to the page.
   */
  public function addToPage() {
    $fonts = array(
      'http://fonts.googleapis.com/css?family=OFL+Sorts+Mill+Goudy+TT&v1',
      'http://fonts.googleapis.com/css?family=Varela&v1'
    );

    foreach ($fonts AS $font) {
      Element::styleSheet($font)->addToHead();
    }

    $this->addToBody();
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
