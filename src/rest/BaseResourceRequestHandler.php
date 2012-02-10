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
namespace conductor\rest;

/**
 * Base implementation of a ResourceRequestHandler.  Implements a handler for
 * all request actions for which returns a 404 response with an empty body.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class BaseResourceRequestHandler implements ResourceRequestHandler {

  public function delete(ResourceRequest $request, ResourceResponse $response) {
    $this->_methodNotAllowed($response);
  }

  public function get(ResourceRequest $request, ResourceResponse $response) {
    $this->_methodNotAllowed($response);
  }

  public function post(ResourceRequest $request, ResourceResponse $response) {
    $this->_methodNotAllowed($response);
  }

  public function put(ResourceRequest $request, ResourceResponse $response) {
    $this->_methodNotAllowed($response);
  }

  private function _methodNotAllowed($response) {
    $response->header('HTTP/1.1 405 Method Not Allowed');

    $impl = new ReflectionClass(get_class($this));
    
    $methods = array('delete', 'get', 'post', 'put');
    $allowed = array();
    foreach ($methods AS $method) {
      $declaringClass = $impl->getMethod($method)
                             ->getDeclaringClass()
                             ->getName();

      if ($declaringClass === $child->getName()) {
        $allowed[] = strtoupper($method);
      }
    }
    $response->header('Allow: ' . implode(', ', $allowed));
  }

}
