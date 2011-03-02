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

use \conductor\admin\AdminGenerator;
use \conductor\admin\AdminTemplate;
use \conductor\generator\BassoonServiceGenerator;
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
   * Get the title for the page with the given id.
   *
   * @param string $pageId
   * @return string
   */
  public static function getPageTitle($pageId = null) {
    // Call conductor init() so that if it hasn't been explicitely intitialized
    // it will be now or will throw an exception if there is a problem
    Conductor::init();

    if ($pageId === null) {
      $pageId = Conductor::$config['pageCfg']['default'];
    }

    if (!isset(Conductor::$config['pageCfg']['pages'][$pageId])) {
      // TODO This is an error, but what is the appropriate behaviour?
      return null;
    }

    return Conductor::$config['pageCfg']['pages'][$pageId]['title'];
  }

  /**
   * Load a page defined in conductor.cfg.xml.
   *
   * @param string $pageId The id given to the page in the config file. If null
   *   (default) then the default page is loaded.
   * @return Fragment containing the page content.
   */
  public static function loadPage($pageId = null) {
    // Call conductor init() so that if it hasn't been explicitely intitialized
    // it will be now or will throw an exception if there is a problem
    Conductor::init();

    if ($pageId === null) {
      $pageId = Conductor::$config['pageCfg']['default'];
    }

    if (!isset(Conductor::$config['pageCfg']['pages'][$pageId])) {
      // TODO This is an error, but what is the appropriate behaviour?
      return null;
    }

    $pageInfo = Conductor::$config['pageCfg']['pages'][$pageId];
    $className = $pageInfo['class'];

    $page = new $className();
    $frag = $page->getFragment();
    return $frag;
  }

  /**
   * Load the admin interface for the models defined in conductor.cfg.xml.
   */
  public static function loadAdmin() {
    // Call conductor init() so that if it hasn't been explicitely intitialized
    // it will be now or will throw an exception if there is a problem
    Conductor::init();

    $jsDir = Conductor::$config['webWritable'] . '/js';
    $jsPath = $jsDir . '/conductor-admin.js';

    if (defined('DEBUG') && DEBUG === false) {
      // Generate and output the admin javascript
      $adminGen = new AdminGenerator(Conductor::$config['models']);
      $adminGen->generate($jsDir);
    }

    // Set the page template to be the admin template
    Conductor::setPageTemplate(new AdminTemplate($jsPath));

    if (Auth::hasPermission('conductor-admin')) {
      $widget = new ModelEditor();
      $widget->addToBody();
    } else {
      self::loadLogin("You are either not logged in or do not have sufficient"
        . " privileges to access the admin panel.");
    }
    Page::dump();
  }

  /**
   * Load the login form.  
   */
  public static function loadLogin($msg = null) {
    $login = new LoginForm($msg);
    $login->addToBody();
  }
}
