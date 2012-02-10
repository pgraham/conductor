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

use \conductor\admin\AdminClient;
use \conductor\auth\AuthorizationException;
use \conductor\jslib\JsLib;
use \conductor\widget\ModelEditor;
use \conductor\widget\LoginForm;

use \reed\FsToWebPathConverter;

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
   * Load the login form.  
   */
  public static function loadLogin($msg = null) {
    $login = new LoginForm($msg);
    $login->addToPage();
  }

  /**
   * Load a page defined in conductor.cfg.xml.
   *
   * @param string $pageId The id given to the page in the config file. If null
   *   (default) then the default page is loaded.
   * @param boolean $async If this loadPage request is part of an asynchronous
   *   request. Default: false.
   * @return Fragment containing the page content.
   */
  public static function loadPage($pageId = null, $async = false) {
    try {
      Conductor::init();

      $page = Conductor::getPage($pageId);
      if ($page === null) {
        header("HTTP/1.1 404 Not Found");
        $notFound = new Fragment(__DIR__ . '/html/404.html');
        echo $notFound;
        exit;
      }

      $className = $page->getClassName();
      $page = new $className();
      $frag = $page->getFragment();
      return $frag;

    } catch (AuthorizationException $e) {
      if ($async) {
        $loginForm = self::_buildLoginForm($e);
        return $loginForm;
      } else {
        self::loadLogin($e->getMessage());
        return null;
      }
    }
  }

  /* Create a login form from the given authorization exception. */
  private static function _buildLoginForm($e) {
    $msg = $e->getMessage();
    if ($msg === null) {
      $msg = 'You must provide credentials with sufficient permissions to'
        . ' perform the requested action.';
    }
    $loginForm = new LoginForm($msg, true);

    if ($e->getUsernameLabel() !== null) {
      $loginForm->setUsernameLabel($e->getUsernameLabel());
      $loginForm->setPasswordLabel($e->getPasswordLabel());
    }
    return $loginForm;
  }
}
