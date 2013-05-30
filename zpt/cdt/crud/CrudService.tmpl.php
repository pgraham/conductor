<?php
namespace /*# actorNs #*/;

use \zeptech\orm\runtime\ValidationException;
use \zpt\rest\Request;
use \zpt\rest\Response;
use \zpt\rest\RestException;
use \zpt\cdt\crud\CrudException;
use \zpt\cdt\crud\Gatekeeper;
use \zpt\cdt\crud\SpfParser;
use \zpt\cdt\exception\AuthException;
use \zpt\cdt\i18n\ModelMessages;
use \zpt\cdt\AuthProvider;
use \zpt\cdt\Session;
use \zpt\orm\Criteria;
use \zpt\orm\PdoExceptionWrapper;
use \StdClass;

/**
 * This is a CRUD service class for a /*# model #*/ class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service
 */
class /*# actorClass #*/ { 

  /** @Injected */
  private $authProvider;

  /** @Injected(ref = /*# gatekeeperBeanId #*/) */
  private $gatekeeper;

  /** @Injected */
  private $session;

  /** @Injected */
  private $spfParser;

  /**
   * @Method post
   * @Uri /*# url #*/
   */
  public function create(Request $request, Response $response) {
    #{if auth ISSET
      $this->checkAuth('write');
    #}

    $transformer = $this->transformerFactory->get('/*# model #*/');

    $params = (array) $request->getData();

    $model = $transformer->fromArray($params);
    $this->gatekeeper->checkCanCreate($model);

    $persister = $this->persisterFactory->get($model);
    $id = $persister->create($model);

    $info = $this->messagesFactory->get('/*# model #*/');
    $response->setData(array(
      'success' => true,
      'id'  => $id,
      'entity' => $transformer->asArray($model),
      'msg' => array(
        'text' => $info->createSuccessMsg(),
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

    $persister = $this->persisterFactory->get('/*# model #*/');
    $transformer = $this->transformerFactory->get('/*# model #*/');
    $qb = $this->queryBuilderFactory->get('/*# model #*/');

    $spf = $this->spfParser->parseRequest($request);
    $this->spfParser->populateQueryBuilder($spf, $qb);

    // Retrieve the models that match the given spf
    $c = $qb->getCriteria();
    $models = $persister->retrieve($c);
    $total = $persister->count($c);

    $data = array();
    foreach ($models AS $model) {
      if ($this->gatekeeper->canRead($model)) {
        $data[] = $transformer->asArray($model);
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

    $persister = $this->persisterFactory->get('/*# model #*/');

    $id = $request->getParameter('id');

    $model = $persister->getById($id);

    if ($model === null) {
      throw new RestException(404);
    }

    // Don't do this inside of a try block that catches generic exception
    // since we want any thrown AuthException to bubble.
    $this->gatekeeper->checkCanRead($model);

    $transformer = $this->transformerFactory->get('/*# model #*/');
    $response->setData($transformer->asArray($model));
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

    $persister = $this->persisterFactory->get('/*# model #*/');
    $model = $persister->getById($id);

    if ($model === null) {
      throw new RestException(404);
    }
      
    // Don't do this inside of a try block that catches generic exception
    // since we want any thrown AuthException to bubble.
    $this->gatekeeper->checkCanWrite($model);

    $transformer = $this->transformerFactory->get('/*# model #*/');
    $transformer->fromArray($params, $model);

    $persister->update($model);

    $info = $this->messagesFactory->get('/*# model #*/');
    $response->setData(array(
      'success' => true,
      'msg' => array(
        'text' => $info->updateSuccessMsg(),
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

    $persister = $this->persisterFactory->get('/*# model #*/');
    $id = $request->getParameter('id');

    $model = $persister->getById($id);
    if ($model === null) {
      throw new RestException(404);
    }

    $this->gatekeeper->checkCanDelete($model);

    $persister->delete($model);

    $info = $this->messagesFactory->get('/*# model #*/');
    $response->setData(array(
      'success' => true,
      'msg' => array(
        'text' => $info->deleteSuccessMsg(),
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
