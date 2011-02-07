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

use \conductor\template\AdminTemplate;
use \conductor\widget\ModelEditor;
use \conductor\widget\LoginForm;

/**
 * This class provides methods for loading various predefined pages.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class PageLoader {

  /**
   * Load a page defined in conductor.cfg.xml.
   *
   * @param string $pageId The id given to the page in the config file. If null
   *   (default) then the default page is loaded.
   */
  public static function loadPage($pageId = null) {
    // TODO
  }

  /**
   * Load the admin interface for the models defined in conductor.cfg.xml.
   */
  public static function loadAdmin() {
    // Call conductor init() so that if it hasn't been explicitely intitialized
    // it will be now or will throw an exception if here is a problem
    Conductor::init();

    // Set the page template to be the admin template
    Conductor::setPageTemplate(new AdminTemplate());

    if (Auth::hasPermission('conductor-admin')) {
      $widget = new ModelEditor();
      $widget->addToBody();
    } else {
      self::loadLogin();
    }
    Page::dump();
  }

  /**
   * Load the login form.  
   */
  public static function loadLogin() {
    $login = new LoginForm();
    $login->addToBody();
  }
}
