<?php
namespace zeptech\dynamic\crud;

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\PdoExceptionWrapper;
use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\Transformer;
use \zeptech\orm\runtime\ValidationException;
use \zeptech\orm\QueryBuilder;

use \zpt\cdt\crud\CrudException;
use \zpt\cdt\Conductor;

${if:gatekeeper ISSET}
  use \${gatekeeper} as Gatekeeper;
${else}
  use \zpt\cdt\crud\DefaultGatekeeper as Gatekeeper;
${fi}

/**
 * This is a CRUD service class for a ${model} class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service( name = ${proxyName} )
 * @CsrfToken conductorsessid
 */
class ${modelName}Crud { 

  private $_gatekeeper;

  public function __construct() {
    $this->_gatekeeper = new Gatekeeper('${model}');
  }

  /**
   * @RequestType post
   */
  public function create(array $params) {
    $transformer = Transformer::get('${model}');
    $model = $transformer->fromArray($params);

    $this->_gatekeeper->checkCanCreate($model);

    try {
      $persister = Persister::get($model);
      $persister->create($model);
    } catch (PdoExceptionWrapper $e) {
      throw new CrudException($e);
    } catch (ValidationException $e) {
      throw new CrudException($e);
    }
  }

  /**
   * Retrieve instances that match the given sort-paging-filtering criteria.
   *
   * @param array $spf
   */
  public function retrieve($spf) {
    $persister = Persister::get('${model}');
    $transformer = Transformer::get('${model}');

    $qb = QueryBuilder::get('${model}');

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
    try {
      $c = $qb->getCriteria();
      $models = $persister->retrieve($c);
      $total = $persister->count($c);

      $data = array();
      foreach ($models AS $model) {
        if ($this->_gatekeeper->canRead($model)) {
          $data[] = $transformer->asArray($model);
        }
      }

      return array(
        'data' => $data,
        'total' => $total
      );
    } catch (PdoExceptionWrapper $e) {
      error_log("Encountered an error process SQL:\n\n{$e->getSql()}");
      throw new CrudException($e);
    } catch (ValidationException $e) {
      throw new CrudException($e);
    }
  }

  /**
   * Retrieve a single instance with the given ID.
   *
   * @param $id
   */
  public function retrieveOne($id) {

    try {
      $persister = Persister::get('${model}');
      $model = $persister->getById($id);

      if ($model === null) {
        // TODO This needs to changed so that the CrudException has a
        // isNotFound() method and the message is built using a ModelInfo actor.
        throw new CrudException('404 Not Found', 'The requested ${display} does not exist');
      }

      $this->_gatekeeper->checkCanRead($model);

      $transformer = Transformer::get('${model}');
      return $transformer->asArray($model);

    } catch (PdoExceptionWrapper $e) {
      throw new CrudException($e);
    } catch (ValidationException $e) {
      throw new CrudException($e);
    }
  }

  /**
   * @RequestType post
   */
  public function update($id, array $params) {
    try {
      $persister = Persister::get('${model}');
      $model = $persister->getById($id);

      if ($model === null) {
      }
      
      $this->_gatekeeper->checkCanWrite($model);

      $transformer = Transformer::get('${model}');
      $transformer->fromArray($params, $model);

      $persister->update($model);
    } catch (PdoExceptionWrapper $e) {
      throw new CrudException($e);
    } catch (ValidationException $e) {
      throw new CrudException($e);
    }
  }

  /**
   * @RequestType post
   */
  public function delete(array $ids) {
    $persister = Persister::get('${model}');

    $c = new Criteria();
    $c->addIn('${idColumn}', $ids);
    $models = $persister->retrieve($c);

    foreach ($models AS $model) {
      $this->_gatekeeper->checkCanDelete($model);
    }

    try {
      foreach ($models AS $model) {
        $persister->delete($model);
      }
    } catch (PdoExceptionWrapper $e) {
      throw new CrudException($e);
    } catch (ValidationException $e) {
      throw new CrudException($e);
    }
  }

}
