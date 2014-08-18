<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace /*# companionNs #*/;

use zeptech\orm\runtime\ValidationException;
use zpt\rest\Request;
use zpt\rest\Response;
use zpt\rest\RestException;
use zpt\cdt\crud\CrudException;
use zpt\cdt\crud\Gatekeeper;
use zpt\cdt\crud\SpfParser;
use zpt\cdt\exception\AuthException;
use zpt\cdt\AuthProvider;
use zpt\cdt\Session;
use zpt\orm\Criteria;
use zpt\orm\PdoExceptionWrapper;
use zpt\orm\Repository;
use StdClass;

/**
 * This is a CRUD service class for a /*# model #*/ class.
 *
 * This class is generated. Do __NOT__ modify this file. Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service
 */
class /*# companionClass #*/
{

	/** @Injected */
	private $authProvider;

	/** @Injected(ref = /*# gatekeeperBeanId #*/) */
	private $gatekeeper;

	/** @Injected */
	private $session;

	/** @Injected */
	private $spfParser;

	private $persister;
	private $transformer;
	private $messages;

	/**
	 * @ctorArg ref = orm
	 */
	public function __construct(Repository $orm) {
		$this->orm = $orm;

		$this->persister = $orm->getPersister('/*# model #*/');
		$this->transformer = $orm->getTransformer('/*# model #*/');
		$this->messages = $orm->getMessageGenerator('/*# model #*/');
	}

	/**
	 * @Method post
	 * @Uri /*# url #*/
	 */
	public function create(Request $request, Response $response) {
		#{if auth ISSET
			$this->checkAuth('write');
		#}

		$params = (array) $request->getData();

		$model = $this->transformer->fromArray($params);
		$this->gatekeeper->checkCanCreate($model);

		$id = $this->persister->create($model);

		$response->setData(array(
			'success' => true,
			'id'	=> $id,
			'entity' => $this->transformer->asArray($model),
			'msg' => array(
				'text' => $this->messages->createSuccessMsg(),
				'type' => 'info'
			)
		));
	}

	/**
	 * Retrieve instances that match the given sort-paging-filtering criteria.
	 *
	 * @Method get
	 * @Uri /*# url #*/
	 */
	public function retrieve(Request $request, Response $response) {
		#{if auth ISSET
			$this->checkAuth('read');
		#}

		$qb = $this->orm->getQueryBuilder('/*# model #*/');

		$spf = $this->spfParser->parseRequest($request);
		$this->spfParser->populateQueryBuilder($spf, $qb);

		// Retrieve the models that match the given spf
		$c = $qb->getCriteria();
		$models = $this->persister->retrieve($c);
		$total = $this->persister->count($c);

		$data = array();
		foreach ($models AS $model) {
			if ($this->gatekeeper->canRead($model)) {
				$data[] = $this->transformer->asArray($model);
			}
		}

		$response->setData(array(
			'data' => $data,
			'total' => $total
		));
	}

	/**
	 * Retrieve a single instance with the given ID.
	 *
	 * @Method get
	 * @Uri /*# url #*//{id}
	 */
	public function retrieveOne(Request $request, Response $response) {
		#{if auth ISSET
			$this->checkAuth('read');
		#}

		$id = $request->getParameter('id');

		$model = $this->persister->getById($id);

		if ($model === null) {
			throw new RestException(404);
		}

		// Don't do this inside of a try block that catches generic exception
		// since we want any thrown AuthException to bubble.
		$this->gatekeeper->checkCanRead($model);

		$response->setData($this->transformer->asArray($model));
	}

	/**
	 * @Method post
	 * @Uri /*# url #*//{id}
	 * @EnforceOrder
	 */
	public function update(Request $request, Response $response) {
		#{if auth ISSET
			$this->checkAuth('write');
		#}

		$id = $request->getParameter('id');
		$params = (array) $request->getData();

		$model = $this->persister->getById($id);

		if ($model === null) {
			throw new RestException(404);
		}

		// Don't do this inside of a try block that catches generic exception
		// since we want any thrown AuthException to bubble.
		$this->gatekeeper->checkCanWrite($model);

		$this->transformer->fromArray($params, $model);

		$this->persister->update($model);

		$response->setData(array(
			'success' => true,
			'msg' => array(
				'text' => $this->messages->updateSuccessMsg(),
				'type' => 'info'
			)
		));
	}

	/**
	 * @Method delete
	 * @Uri /*# url #*//{id}
	 */
	public function delete(Request $request, Response $response) {
		#{if auth ISSET
			$this->checkAuth('write');
		#}

		$id = $request->getParameter('id');

		$model = $this->persister->getById($id);
		if ($model === null) {
			throw new RestException(404);
		}

		$this->gatekeeper->checkCanDelete($model);

		$this->persister->delete($model);

		$response->setData(array(
			'success' => true,
			'msg' => array(
				'text' => $this->messages->deleteSuccessMsg(),
				'type' => 'info'
			)
		));
	}

	/*
	 * ===========================================================================
	 * Dependency injection setters.
	 * ===========================================================================
	 */

	public function setAuthProvider(AuthProvider $authProvider) {
		$this->authProvider = $authProvider;
	}

	public function setGatekeeper(Gatekeeper $gatekeeper) {
		$this->gatekeeper = $gatekeeper;
	}

	public function setSession(Session $session)
	{
			$this->session = $session;
	}

	public function setSpfParser(SpfParser $spfParser)
	{
			$this->spfParser = $spfParser;
	}

	/*
	 * ===========================================================================
	 * Private helpers.
	 * ===========================================================================
	 */

	#{if auth ISSET
		private function checkAuth($level) {
			if (!$this->authProvider->hasPermission('/*# auth #*/', $level)) {
				$msg = _L('auth.NotAuthorized');
				throw new AuthException(AuthException::NOT_AUTHORIZED, $msg);
			}
		}
	#}

}
