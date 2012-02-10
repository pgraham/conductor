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

use \conductor\jslib\JQuery;
use \conductor\jslib\JQueryCookie;
use \conductor\jslib\JQueryUi;
use \conductor\Conductor;
use \conductor\Resource;
use \conductor\ResourceSet;

/**
 * This class encapsulates a the base set of resources for the platform.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class BaseResources extends ResourceSet {

  public function __construct($theme) {
    // Stylesheets
    // -------------------------------------------------------------------------
    $this->addSheets(array(
      'reset.css',
      'cdt.css',
      'login.css'
    ));

    // JQuery and plugins
    // -------------------------------------------------------------------------

    $this->addJsLib(new JQuery());
    $this->addJsLib(new JQueryCookie());
    $this->addJsLib(new JQueryUi(array('theme' => $theme)));

    // jquery.working.js
    $this->addScript('jquery.working.js');
    $this->addImage('working.gif');

    // JavaScripts
    // -------------------------------------------------------------------------
    $this->addScripts(array(
      'utility.js',
      'jquery-dom.js',
      'login.js',
      'conductor.js'
    ));

    // Services
    // -------------------------------------------------------------------------
    $this->addServices(array(
      'conductor\Service',
      'conductor\LoginService',
      'conductor\ContentService'
    ));
  }
}
