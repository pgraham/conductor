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
use \zpt\rest\BaseRequestHandler;
use \zpt\rest\RequestHandler;
use \zpt\rest\Request;
use \zpt\rest\Response;
use \zpt\cdt\di\Injector;
use \Exception;
use \ReflectionClass;

class HtmlRequestHandler extends BaseRequestHandler implements RequestHandler {

  private $_beanId;
  private $_htmlProvider;

  /**
   * Create a new HtmlRequestHandler for the given Page definition class
   *
   * @param string $pageDef
   */
  public function __construct($beanId) {
    $this->_beanId = $beanId;
  }

  /**
   * @Override
   */
  public function get(Request $request, Response $response) {
    $page = Page::getInstance();

    $this->_htmlProvider = Injector::getBean($this->_beanId);
    $this->_htmlProvider->populate($page, $request->getQuery());

    $response->setData($page);
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }

}
