<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\cdt\rest;

use \zeptech\rest\ExceptionHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \zeptech\rest\RestException;
use \Exception;

/**
 * REST server exception handler for AuthExceptions. Builds a 401 response.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AuthExceptionHandler implements ExceptionHandler
{

    public function handleException(
        Exception $e,
        Request $request,
        Response $response
    ) {

      // TODO Move this logic somewhere so that it can be reused
      $response->clearHeaders();

      $code = 401;
      $hdrMsg = _L('http.status.header.401');
      $msg = _L('http.status.message.401');

      $response->header("HTTP/1.1 $code $hdrMsg");
      $response->setData($msg);


    }
}
