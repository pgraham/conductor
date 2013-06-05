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

use \zpt\rest\ExceptionHandler;
use \zpt\rest\Request;
use \zpt\rest\Response;
use \zpt\rest\RestException;
use \zpt\cdt\exception\PdoExceptionWrapperParser;
use \Exception;

/**
 * REST server exception handler for PdoExceptions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PdoExceptionHandler implements ExceptionHandler
{

    /** @Injected */
    private $companionLoader;

    public function handleException(
        Exception $e,
        Request $request,
        Response $response
    ) {

        $exceptionParser = new PdoExceptionWrapperParser($e); 
        $modelMessages = $this->messagesFactory->get($e->getModelClass());
        $modelMessages = $this->companionLoader->get(
            'zpt\dyn\i18n',
            $e->getModelClass()
        );

        $response->clearHeaders();
        $hdrMsg = _L('http.status.header.403');
        $msg = _L('http.status.message.403'); // Default message
       
        $info = $exceptionParser->getResponseInfo();
        if ($exceptionParser->isDuplicate()) {
          $msg = $modelMessages->duplicateMsg($info['field'], $info['value']);

        } else if ($exceptionParser->isInvalidFilter()) {
          $msg = $modelMessages->invalidFilterMsg($info['filter']);

        } else if ($exceptionParser->isInvalidSort()) {
          $msg = $modelMessages->invalidSortMsg($info['sort']);

        } else if ($exceptionParser->isNotNullViolation()) {
          $msg = $modelMessages->notNullMsg($info['field']);

        }

        $response->header("HTTP/1.1 403 $hdrMsg");
        $response->setData($msg);
    }

    public function setCompanionLoader(CompanionLoader $companionLoader) {
        $this->companionLoader = $companionLoader;
    }
}
