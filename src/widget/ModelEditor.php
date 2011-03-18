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

use \conductor\Conductor;

use \oboe\item;
use \oboe\BaseList;
use \oboe\Composite;
use \oboe\Div;
use \oboe\Heading;
use \oboe\ListEl;

/**
 * This class builds an administration interface for manipulating the given set
 * of model classes.  The structure contained by this widget is just a shell
 * that will be populated by the generated conductor-admin.js script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/widget
 */
class ModelEditor extends Composite implements Item\Body {

  public function __construct() {
    $this->initElement(new Div('cdt-Admin'));

    $menu = new Div('menu');
    $menu->add(new ListEl(BaseList::UNORDERED));
    $this->elm->add($menu);

    $ctnt = new Div('ctnt');
    $this->elm->add($ctnt);
  }
}
