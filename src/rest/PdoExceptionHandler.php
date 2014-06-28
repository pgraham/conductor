<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License. The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\rest;

use zpt\cdt\exception\PdoExceptionWrapperParser;
use zpt\rest\ExceptionHandler;
use zpt\rest\Request;
use zpt\rest\Response;
use zpt\rest\RestException;
use zpt\opal\CompanionLoader;
use Exception;

/**
 * REST server exception handler for PdoExceptions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PdoExceptionHandler implements ExceptionHandler
{

	private $companionLoader;

	public function __construct(CompanionLoader $companionLoader) {
		$this->companionLoader = $companionLoader;
	}

	public function handleException(
		Exception $e,
		Request $request,
		Response $response
	) {

		$exceptionParser = new PdoExceptionWrapperParser($e);
		$modelMessages = $this->companionLoader->get(
			'zpt\dyn\i18n',
			$e->getModelClass()
		);

		$response->clearHeaders();
		$status = 500;
		$msg = _L("http.status.message.500"); // Default message

		$info = $exceptionParser->getResponseInfo();
		if ($exceptionParser->isDuplicate()) {
			$status = 403;
			$msg = $modelMessages->duplicateMsg($info['field'], $info['value']);

		} else if ($exceptionParser->isInvalidFilter()) {
			$status = 403;
			$msg = $modelMessages->invalidFilterMsg($info['filter']);

		} else if ($exceptionParser->isInvalidSort()) {
			$status = 403;
			$msg = $modelMessages->invalidSortMsg($info['sort']);

		} else if ($exceptionParser->isNotNullViolation()) {
			$status = 403;
			$msg = $modelMessages->notNullMsg($info['field']);

		}

		$reasonPhrase = _L("http.status.header.$status");
		$response->header("HTTP/1.1 $status $reasonPhrase");
		$response->setData($msg);
	}
}
