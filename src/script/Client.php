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
namespace conductor\script;

use \conductor\compile\ClientCompiler;
use \conductor\compile\Compilable;
use \conductor\Conductor;
use \conductor\Resource;

use \reed\WebSitePathInfo;

/**
 * This class compiles the conductor client javascript.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Client implements Compilable {

  private $_resources = array();

  public function __construct() {
    // Don't do auto compile here as this class is instantiated by the site
    // compiler.  Auto compile is instead done in the addToPage method when it
    // is more certain that we are operating in a Conductor::init environment.
    $this->_resources['working'] = new Resource('working.gif');
    $this->_resources['reset']   = new Resource('reset.css');
    $this->_resources['utility'] = new Resource('utility.js');
    $this->_resources['client'] = new Resource('conductor.js');
    $this->_resources['dom'] = new Resource('jquery-dom.js');
  }

  public function addToPage() {
    if (Conductor::isDebug()) {
      $this->compile(Conductor::$config['pathInfo']);
    }

    $this->_resources['reset']->addToPage();
    $this->_resources['dom']->addToPage();
    $this->_resources['utility']->addToPage();
    $this->_resources['client']->addToPage();
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $compiler = new ClientCompiler($this);
    $compiler->compile($pathInfo, $values);
  }

  public function getResources() {
    return $this->_resources;
  }
}
