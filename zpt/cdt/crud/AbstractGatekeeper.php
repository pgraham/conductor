<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\crud;

use \zpt\cdt\exception\AuthException;
use \zpt\opal\CompanionLoader;


/**
 * Base implementation for Gatekeepers.  Implements the four checkCan* methods,
 * Leave the four can*() methods for the implementations.
 *
 * TODO Use Transformer instead of ActorFactory
 * TODO This is not the right package for this class, where does it actually
 *      belong?
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class AbstractGatekeeper implements Gatekeeper {

	private $companionLoader;

	public function __construct(CompanionLoader $companionLoader = null) {
		if ($companionLoader === null) {
			$companionLoader = new CompanionLoader();
		}
		$this->companionLoader = $companionLoader;
	}

	public function checkCanCreate($model) {
		if (!$this->canCreate($model)) {
			throw $this->newAuthException($model, 'create');
		}
	}

	public function checkCanDelete($model) {
		if (!$this->canDelete($model)) {
			throw $this->newAuthException($model, 'delete');
		}
	}

	public function checkCanRead($model) {
		if (!$this->canRead($model)) {
			throw $this->newAuthException($model, 'read');
		}
	}

	public function checkCanWrite($model) {
		if (!$this->canWrite($model)) {
			throw $this->newAuthException($model, 'write');
		}
	}

	protected function newAuthException($model, $action) {
		$transformer = $this->companionLoader->get(
			'zpt\dyn\orm\transformer',
			$model
		);

		$id = $transformer->getId($model);
		$msg = "Unable to $action " . get_class($model);
		if ($id) {
			$msg .= " with id $id";
		}

		$msg .= ": Permission Denied";

		// TODO Throw a RestException instead
		return new AuthException(AuthException::NOT_AUTHORIZED, $msg);
	}
}
