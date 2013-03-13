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
use \zeptech\rest\RestExceptionHandler;
use \zpt\cdt\L10N;
use \Exception;

/**
 * Replacement for the default RestExceptionHandler which will detect messages
 * that are localization codes and replace them with their localized message.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LocalizedRestExceptionHandler implements ExceptionHandler
{

    private $defaultHandler;
    
    /**
     * Create a new LocalizedRestExceptionHandler.  An instance of the
     * default exception handler is required to build the actual response.
     *
     * @param RestExceptionHandler $defaultHandler
     */
    public function __construct(RestExceptionHandler $defaultHandler)
    {
        $this->defaultHandler = $defaultHandler;
    }

    /**
     * Handle a RestException.
     */
    public function handleException(
        Exception $e,
        Request $request,
        Response $response
    ) {

        $msg = $e->getMessage();
        if (L10N::strExists($msg)) {
          $e->setMessage(_L($msg));
        }

        $this->defaultHandler->handleException($e, $request, $response);
    }
}
