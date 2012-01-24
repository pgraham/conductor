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
 */
namespace conductor\resources;

use \conductor\Resource;
use \oboe\Element;

/**
 * This class encapsulates resource inclusion and compilation for javascript
 * application support.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class JsAppResources {

  private $_toCompile;
  private $_toInclude;
  private $_fonts;

  public function __construct() {
    $this->_fonts = 'http://fonts.googleapis.com/css?family=Lato:300';
    $css = array(
      new Resource('conductor-app.css')
    );

    $js = array(
      new Resource('layout.js'),
      new Resource('layout-fill.js'),
      new Resource('widget-section.js'),
      new Resource('widget-collapsible.js'),
      new Resource('widget-form.js'),
      new Resource('widget-list.js'),
      new Resource('component-configurationEditor.js'),
      new Resource('conductor-app.js')
    );

    $this->_toCompile = array_merge($css, $js);
    $this->_toInclude = array_merge($css, $js);
  }

  public function compile() {
    foreach ($this->_toCompile AS $r) {
      $r->compile();
    }
  }

  public function inc() {
    Element::css($this->_fonts)->addToHead();
    foreach ($this->_toInclude AS $r) {
      $r->addToPage();
    }
  }
}
