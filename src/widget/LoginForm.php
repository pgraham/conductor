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

use \oboe\Composite;
use \oboe\Form;
use \oboe\form\Div;
use \oboe\form\TextInput;
use \oboe\form\Password;
use \oboe\form\Submit;
use \oboe\item;

/**
 * This class encapsulates a stylable login form.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/widget
 */
class LoginForm extends Composite implements item\Body {

  /**
   * Create a new login form.
   *
   * @param string $msg An optional message to display to the user about why
   *   they are seeing a login form.
   */
  public function __construct($msg = null) {
    $this->initElement(new Form('login'));
    $this->elm->setClass('cdt-LoginForm');

    if ($msg !== null) {
      $msgDiv = new Div(null, 'cdt-Error');
      $msgDiv->add($msg);
      $this->elm->add($msgDiv);
    }

    $usernameLbl = new Div(null, 'cdt-Label');
    $usernameLbl->add("Username:");
    $username = new TextInput('uname');
    $username->setClass('cdt-TextInput');

    $passwordLbl = new Div(null, 'cdt-Label');
    $passwordLbl->add("Password:");
    $password = new Password('pw');
    $password->setClass('cdt-TextInput');

    $submit = new Submit('Login');
    $submit->setClass('cdt-Submit');

    $this->elm->add($usernameLbl);
    $this->elm->add($username);

    $this->elm->add($passwordLbl);
    $this->elm->add($password);

    $this->elm->add($submit);
  }
}
