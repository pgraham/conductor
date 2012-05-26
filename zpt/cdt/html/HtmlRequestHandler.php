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
namespace zpt\cdt\html;

use \oboe\struct\FlowContent;
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
  private $_htmlProvider;

  /**
   * Create a new HtmlRequestHandler for the given Page definition class
   *
   * @param string $pageDef
   */
  public function __construct($pageDef) {
    $this->_pageDef = $pageDef;
  }

  /**
   * @Override
   */
  public function get(Request $request, Response $response) {
    $page = Page::getInstance();

    $this->_htmlProvider = HtmlProvider::get($this->_pageDef);
    $this->_htmlProvider->populate($page, $request->getQuery());

    $response->setData($page);
  }

}
