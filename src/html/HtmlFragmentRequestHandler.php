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

use \zpt\rest\BaseRequestHandler;
use \zpt\rest\RequestHandler;
use \zpt\rest\Request;
use \zpt\rest\Response;
use \zpt\cdt\di\Injector;

/**
 * RESTful request handler for retriving a requested Page fragment.  A page
 * fragment is the return value of a page definition's getContent() method
 * without a template or any HTML boilerplate.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class HtmlFragmentRequestHandler extends BaseRequestHandler
    implements RequestHandler
{

  /** @Injected */
  private $_authProvider;

  private $_beanId;

  /**
   * Create a new  HtmlFragmentRequestHandler for the given Page definition
   * class.
   *
   * @param string $pageDef
   */
  public function __construct($beanId) {
    $this->_beanId = $beanId;
  }

  public function get(Request $request, Response $response) {
    $htmlProvider = Injector::getBean($this->_beanId);
    $htmlProvider->setAuthProvider($this->_authProvider);

    $frag = $htmlProvider->getFragment($request);
    if (!is_array($frag)) {
      $frag = array($frag);
    }

    $fragStr = '';
    foreach ($frag as $f) {
      $fragStr .= $f;
    }
    $response->setData($fragStr);
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }
}
