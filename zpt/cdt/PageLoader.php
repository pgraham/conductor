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
namespace zpt\cdt;

use \oboe\Element;
use \zpt\cdt\html\Page;

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

    $page = Conductor::getPage($pageId);
    if ($page === null) {
      return null;
    }
    return $page->getTitle();
  }

  /**
   * Add DateJs to the page.
   */
  public static function loadDateJs() {
    Element::js(_P('/jslib/datejs/date.js'))->addToHead();
  }

  /**
   * Add jQuery to the page.
   */
  public static function loadJQuery() {
    $jQueryName = 'jquery.min.js';
    if (Conductor::isDevMode()) {
      $jQueryName = 'jquery.js';
    }
    $jqPath = 'http://ajax.googleapis.com/ajax/libs/jquery/' .
      Conductor::JQUERY_VERSION . "/$jQueryName";
    Element::js($jqPath)->addToHead();
  }

  /**
   * Add jQuery Cookie to the page.
   */
  public static function loadJQueryCookie() {
    Element::js(_P('/jslib/jquery-cookie/jquery.cookie.js'))->addToHead();
  }

  /**
   * Add jQuery UI to the page.
   */
  public static function loadJQueryUi($theme = null) {
    if ($theme === null) {
      $theme = 'base';
    }
    Element::css(_P('/jslib/jquery-ui/jquery.ui.css'))->addToHead();
    Element::css(_P("/jslib/jquery-ui/themes/$theme/jquery.ui.theme.css"))
      ->addToHead();

    Element::js(_P('/jslib/jquery-ui/external/globalize.js'))->addToHead();
    Element::js(_P('/jslib/jquery-ui/jquery.ui.js'))->addToHead();

  }

  public static function loadJsAppSupport() {
    // Add JsApp javascript libraries
    Element::js(_P('/jslib/raphael/raphael.js'))->addToHead();

    Element::css(_P('/css/conductor-app.css'))->addToHead();

    $scripts = array(
      '/js/data-store.js',
      '/js/data-crudProxy.js',
      '/js/layout-hblock.js',
      '/js/widget-section.js',
      '/js/widget-collapsible.js',
      '/js/widget-dialog.js',
      '/js/widget-floatingmenu.js',
      '/js/widget-form.js',
      '/js/widget-pager.js',
      '/js/widget-list.js',
      '/js/widget-icon.js',
      '/js/widget-download.js',
      '/js/conductor-app.js'
    );
    foreach ($scripts as $js) {
      Element::js(_P($js))->addToHead();
    }
  }

  /**
   * Load the login form.  This method should not be used in conjuction with
   * any other load*(...) methods provided by this class.
   */
  public static function loadLogin($msg = null) {
    Element::css('http://fonts.googleapis.com/css?family=Sorts+Mill+Goudy|Varela')->addToHead();
    Element::css(_P('/css/login.css'))->addToHead();

    self::loadJQuery();
    Element::js(_P('/js/login.js'))->addToHead();

    $login = new LoginForm($msg);
    $login->addToBody();
    Page::dump();
  }
}
