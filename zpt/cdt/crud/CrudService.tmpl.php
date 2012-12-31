<?php
namespace ${actorNs};

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\PdoExceptionWrapper;
use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\Transformer;
use \zeptech\orm\runtime\ValidationException;
use \zeptech\orm\QueryBuilder;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \zpt\cdt\crud\CrudException;
use \zpt\cdt\crud\Gatekeeper;
use \zpt\cdt\exception\AuthException;
use \zpt\cdt\i18n\ModelMessages;
use \zpt\cdt\AuthProvider;
use \zpt\cdt\Session;
use \StdClass;

/**
 * This is a CRUD service class for a ${model} class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service
 */
class ${actorClass} { 

  /** @Injected(ref = ${gatekeeperBeanId}) */
  private $_gatekeeper;

  /** @Injected */
  private $authProvider;

  /** @Injected */
  private $session;

  private $_info;

  public function __construct() {
    $this->_info = ModelMessages::get('${model}');
  }

  /**
   * @Method post
   * @Uri ${url}
   */
  public function create(Request $request, Response $response) {
    ${if:auth ISSET}
      $this->checkAuth('write');
    ${fi}

    $transformer = Transformer::get('${model}');

    $params = (array) $request->getData();

    $model = $transformer->fromArray($params);
    $this->_gatekeeper->checkCanCreate($model);

    $persister = Persister::get($model);
    $id = $persister->create($model);

    $response->setData(array(
      'success' => true,
      'id'  => $id,
      'msg' => array(
        'text' => $this->_info->createSuccessMsg(),
        'type' => 'info'
      )
    ));
  }

  /**
   * Retrieve instances that match the given sort-paging-filtering criteria.
   *
   * @Method get
   * @Uri ${url}
   */
  public function retrieve(Request $request, Response $response) {
    ${if:auth ISSET}
      $this->checkAuth('read');
    ${fi}

    $persister = Persister::get('${model}');
    $transformer = Transformer::get('${model}');
    $qb = QueryBuilder::get('${model}');

    $query = $request->getQuery();
    $spf = isset($query['spf'])
      ? json_decode($query['spf'])
      : new StdClass();

    // Apply paging, sorting and filters to the criteria
    if (isset($spf->filter)) {
      foreach ($spf->filter AS $column => $value) {
        $qb->addFilter($column, $value);
      }
    }

    if (isset($spf->sort)) {
      foreach ($spf->sort AS $sort) {
        
        $qb->addSort($sort->field, $sort->dir);
      }
    }

    if (isset($spf->page)) {
      $limit = $spf->page->limit;
      $offset = isset($spf->page->offset)
        ? $spf->page->offset
        : null;

      $qb->setLimit($limit, $offset);
    }

    // Retrieve the models that match the given spf
    $c = $qb->getCriteria();
    $models = $persister->retrieve($c);
    $total = $persister->count($c);

    $data = array();
    foreach ($models AS $model) {
      if ($this->_gatekeeper->canRead($model)) {
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
   * @Uri ${url}/{id}
   */
  public function retrieveOne(Request $request, Response $response) {
    ${if:auth ISSET}
      $this->checkAuth('read');
    ${fi}

    $persister = Persister::get('${model}');

    $id = $request->getParameter('id');

    $model = $persister->getById($id);

    if ($model === null) {
      $this->_notFound($response);
      return;

      // TODO This needs to changed so that the CrudException has a
      // isNotFound() method and the message is built using a ModelInfo actor.
      //throw new CrudException('404 Not Found', 'The requested ${singular} does not exist');
    }

    // Don't do this inside of a try block that catches generic exception
    // since we want any thrown AuthException to bubble.
    $this->_gatekeeper->checkCanRead($model);

    $transformer = Transformer::get('${model}');
    $response->setData($transformer->asArray($model));
  }

  /**
   * @Method post
   * @Uri ${url}/{id}
   */
  public function update(Request $request, Response $response) {
    ${if:auth ISSET}
      $this->checkAuth('write');
    ${fi}

    $id = $request->getParameter('id');
    $params = (array) $request->getData();

    if (isset($params['cdtOrderToken'])) {
      $curTokKey = "${cdtOrderTokenKey}_$id";
      $curTok = $this->session->get($curTokKey);
      if ($curTok === null || $curTok < $params['cdtOrderToken']) {
        $this->session->set($curTokKey, $params['cdtOrderToken']);
      } else {
        $response->setData(array(
          'success' => true
        ));
        return;
      }
    }

    $persister = Persister::get('${model}');
    $model = $persister->getById($id);

    if ($model === null) {
      $this->_notFound($e);
      return;
    }
      
    // Don't do this inside of a try block that catches generic exception
    // since we want any thrown AuthException to bubble.
    $this->_gatekeeper->checkCanWrite($model);

    $transformer = Transformer::get('${model}');
    $transformer->fromArray($params, $model);

    $persister->update($model);

    $response->setData(array(
      'success' => true,
      'msg' => array(
        'text' => $this->_info->updateSuccessMsg(),
        'type' => 'info'
      )
    ));
  }

  /**
   * @Method delete
   * @Uri ${url}/{id}
   */
  public function delete(Request $request, Response $response) {
    ${if:auth ISSET}
      $this->checkAuth('write');
    ${fi}

    $persister = Persister::get('${model}');

    $id = $request->getParameter('id');

    $c = new Criteria();
    $c->addEquals('${idColumn}', $id);

    $model = $persister->retrieveOne($c);

    $this->_gatekeeper->checkCanDelete($model);

    $persister->delete($model);

    $response->setData(array(
      'success' => true,
      'msg' => array(
        'text' => $this->_info->deleteSuccessMsg(),
        'type' => 'info'
      )
    ));
  }

  public function setAuthProvider(AuthProvider $authProvider) {
    $this->authProvider = $authProvider;
  }

  public function setGatekeeper(Gatekeeper $gatekeeper) {
    $this->_gatekeeper = $gatekeeper;
  }

  public function setSession(Session $session)
  {
      $this->session = $session;
  }

  ${if:auth ISSET}
    private function checkAuth($level) {
      if (!$this->authProvider->hasPermission('${auth}', $level)) {
        $msg = _L('auth.NotAuthorized');
        throw new AuthException(AuthException::NOT_AUTHORIZED, $msg);
      }
    }
  ${fi}

}
