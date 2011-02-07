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
 * @package conductor/template
 */
namespace conductor\template;

use \Oboe\Div;
use \Oboe\Heading;

/**
 * This class is the template for conductor's generated administration
 * interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/template
 */
class AdminTemplate implements \conductor\Template {

  private $_cc, $_dc;

  public function __construct() {
    $wrap = new Div('wrap');
    $wrap->addToBody();

    $head = new Div('head');
    $wrap->add($head);

    $menu = new Div('menu');
    $wrap->add($menu);

    $ctnt = new Div('ctnt');
    $wrap->add($ctnt);

    $this->_cc = $ctnt;
    $this->_dc = new Div();

    $this->_populateHead($head);
    $this->_populateMenu($menu);
    $this->_populateCtnt($ctnt);
  }

  public function getContentContainer() {
    return $this->_cc;
  }

  public function getDebugContainer() {
    return $this->_dc;
  }

  public function getBaseTitle() {
    return "Conductor Administration";
  }

  private function _populateHead($head) {
    $head->add(new Heading($this->getBaseTitle()));
  }

  private function _populateMenu($menu) {
  }

  private function _populateCtnt($ctnt) {
  }
}
