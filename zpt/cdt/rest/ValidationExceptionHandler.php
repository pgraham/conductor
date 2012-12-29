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
use \zpt\cdt\i18n\ModelMessages;
use \Exception;

/**
 * REST server exception handler for ValidationExceptions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ValidationExceptionHandler implements ExceptionHandler
{

    public function handleException(
        Exception $e,
        Request $request,
        Response $response
    ) {

      $modelMessages = ModelMessages::get($e->getModelClass);

      $msgs = $e->getMessages();
      if (count($msgs) === 1) {
        $msg = $msgs[0];
      } else {
        $msg = array(
          'msg' => $modelMessages->invalidEntityMsg(),
          'msgs' => $msgs
        );
      }

      $hdrMsg = _L('http.status.header.403');

      $response->header("HTTP/1.1 403 $hdrMsg");
      $response->setData($msg);

    }

}

