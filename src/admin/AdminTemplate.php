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
 * @package conductor/admin
 */
namespace conductor\admin;

use \conductor\Conductor;
use \conductor\template\PageTemplate;
use \Oboe\Div;
use \Oboe\Heading;
use \Oboe\Head\Javascript;
use \Oboe\Head\StyleSheet;

/**
 * This class is the template for conductor's generated administration
 * interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/admin
 */
class AdminTemplate implements PageTemplate {

  private $_cc, $_dc;

  public function __construct($jsPath = null, $cssPath = null) {
    if ($jsPath !== null) {
      $adminJs = new Javascript($jsPath);
      $adminJs->addToHead();
    }

    if ($cssPath !== null) {
      $styleSheet = new StyleSheet($cssPath);
      $styleSheet->addToHead();
    }

    $wrap = new Div('wrap');
    $wrap->addToBody();

    $head = new Div('head');
    $head->add(new Heading($this->getBaseTitle()));
    $wrap->add($head);

    $menu = new Div('menu');
    $wrap->add($menu);

    $ctnt = new Div('ctnt');
    $wrap->add($ctnt);

    $this->_cc = $ctnt;
    $this->_dc = new Div();
  }

  public function getContentContainer() {
    return $this->_cc;
  }

  public function getDebugContainer() {
    return $this->_dc;
  }

  public function getBaseTitle() {
    return Conductor::$config['title'] . " Administration";
  }
}
