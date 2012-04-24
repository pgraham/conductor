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
namespace conductor;

use \oboe\Element;
use \zeptech\anno\Annotations;
use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \Exception;
use \ReflectionClass;

class HtmlRequestHandler extends BaseRequestHandler implements RequestHandler {

  private $_pageDef;

  /**
   * Create a new HtmlRequestHandler for the given Page definition class
   *
   * @param string $pageDef
   */
  public function __construct($pageDef) {
    $this->_pageDef = new ReflectionClass($pageDef);
  }

  /**
   * @Override
   */
  public function get(Request $request, Response $response) {
    $page = Page::getInstance();
    $this->_parsePage($page);

    $response->setData($page);
  }

  private function _parsePage(Page $page) {
    global $asWebPath;

    $anno = new Annotations($this->_pageDef);
    if (!isset($anno['page'])) {
      throw new Exception("$this->_pageDef is not a page definition");
    }

    $page->setPageTitle($anno['page']['title']);

    if (isset($anno['auth'])) {
      if (!Auth::hasPermission('cdt-admin')) {
        PageLoader::loadLogin();
        exit;
      }
    }

    // Add base javascript which contains compiled site specific functions and
    // base css
    Element::js($asWebPath('/js/base.js'))->addToHead();
    Element::css($asWebPath('/css/reset.css'))->addToHead();
    Element::css($asWebPath('/css/cdt.css'))->addToHead();

    // JQuery and plugins
    PageLoader::loadJQuery();
    PageLoader::loadJQueryCookie();
    PageLoader::loadJQueryUi($anno['page']['uitheme']);

    Element::js($asWebPath('/js/jquery.working.js'))->addToHead();

    // Javascripts
    Element::js($asWebPath('/js/utility.js'))->addToHead();
    Element::js($asWebPath('/js/jquery-dom.js'))->addToHead();
    Element::js($asWebPath('/js/conductor.js'))->addToHead();

    if (isset($anno['jsappsupport'])) {
      $theme = null;
      if (isset($anno['jsappsupport']['theme'])) {
        $theme = $anno['jsappsupport']['theme'];
      }
      PageLoader::loadJsAppSupport($theme);
    }

    if (isset($anno['font'])) {
      $fonts = $anno['font'];
      if (!is_array($fonts)) {
        $fonts = array($fonts);
      }
      $fonts = implode('|', str_replace(' ', '+', $fonts));

      Element::css("http://fonts.googleapis.com/css?family=$fonts")
        ->addToHead();
    }

    if (isset($anno['css'])) {
      $sheets = $anno['css'];
      if (!is_array($sheets)) {
        $sheets = array($sheets);
      }

      foreach ($sheets as $css) {
        if (substr($css, 0, 1) === '/') {
          Element::css($asWebPath($css))->addToHead();
        } else {
          Element::css($asWebPath("/css/$css"))->addToHead();
        }
      }
    }

    if (isset($anno['script'])) {
      $scripts = $anno['script'];
      if (!is_array($scripts)) {
        $scripts = array($scripts);
      }
      
      foreach ($scripts as $js) {
        if (substr($js, 0, 1) === '/') {
          Element::js($asWebPath($js))->addToHead();
        } else {
          Element::js($asWebPath("/js/$js"))->addToHead();
        }
      }
    }
  }

}
