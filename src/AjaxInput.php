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
 * @package conductor
 */
namespace conductor;

use \Oboe\Composite;
use \Oboe\Form;
use \Oboe\Item;

/**
 * This class wraps a Oboe\Item\Form implementation and allows it be added to
 * an element composite that only accepts Oboe\Item\Body implementations.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class AjaxInput extends Composite implements Item\Body {

  private $_input;

  public function __construct(Form\Input $wrapped) {
    $form = new Form(null, '#');
    $form->add($wrapped);

    $this->initElement($form);
  }

  public function setClass($class) {
    $this->elm->setClass($class);
  }
}
