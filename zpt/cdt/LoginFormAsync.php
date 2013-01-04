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

use \oboe\Composite;
use \oboe\Element;

/**
 * This class encapsulates an asynchronous login form that is added to a page
 * but not displayed initially.  This allows the login form to be pre-populate
 * via browser auto-complete.  It will be displayed dynamically when an Ajax
 * request returns with a 401 status.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LoginFormAsync extends Composite
{

    public function __construct()
    {
        $form = Element::form()
            ->addClass('login')
            ->setStyle('display', 'none')
            ->add(Element::div()->addClass('cdt-msg')->addClass('error'))
            ->add(
                Element::label(_L('lbl.username'))
                    ->setAttribute('for', 'uname')
            )
            ->add(Element::textInput('uname')->setId('uname'))
            ->add(
                Element::label(_L('lbl.password'))
                    ->setAttribute('for', 'pw')
            )
            ->add(Element::password('pw')->setId('pw'));

        $this->initElement($form);
    }
}
