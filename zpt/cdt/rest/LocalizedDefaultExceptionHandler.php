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
use \Exception;

/**
 * Replacement for the DefaultExceptionHandler provided by php-rest-server to
 * return a localized message body.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LocalizedDefaultExceptionHandler implements ExceptionHandler
{

    public function handleException(
        Exception $e,
        Request $request,
        Response $response
    ) {

      $hdrMsg = _L('http.status.header.500');
      $response->header("HTTP/1.1 500 $hdrMsg");
      $response->setData(_L('http.status.message.500'));
    }
}
