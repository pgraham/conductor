<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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

use \zpt\oobo\attr\CanSubmit;
use \zpt\oobo\Composite;
use \zpt\oobo\Element;

/**
 * This class encapsulates a stylable login form.
 *
 * TODO Inline this class into PageLoader
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LoginForm extends Composite {

  /**
   * Create a new login form.
   *
   * @param string $caption An optional message to display to the user about why
   *   they are seeing a login form.
   */
  public function __construct($caption = null) {
    $this->initElement(
      Element::form()
        ->setId('login')
        ->setMethod(CanSubmit::METHOD_POST)
        ->setAction(_P('/login'))
        ->setClass('cdt-LoginForm')
    );

    if ($caption !== null) {
      $this->elm->add(
        Element::div()
          ->addClass('cdt-Caption')
          ->add($caption)
      );
    }

    $this->elm
      ->add(Element::label('Username:')->forInput('uname'))
      ->add(Element::textInput('uname'))
      ->add(Element::label('Password:')->forInput('pw'))
      ->add(Element::password('pw'))
      ->add(Element::submit('Login'));
  }
}
