<?php
namespace ${ns};

use \clarinet\ActorFactory;
use \clarinet\Persister;
use \clarinet\Criteria;

${if:gatekeeper ISSET}
  use \${gatekeeper} as Gatekeeper;
${else}
  use \conductor\crud\DefaultGatekeeper as Gatekeeper;
${fi}
use \conductor\Conductor;

/**
 * This is CRUD service class for a ${modelName} class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service( name = ${className} )
 * @CsrfToken conductorsessid
 * @Requires ${autoloader}
 */
class ${className} {

  private $_gatekeeper;

  public function __construct() {
    // Ensure that conductor is initialized
    Conductor::init();

    $this->_gatekeeper = new Gatekeeper('${model}');
  }

  /**
   * @RequestType post
   */
  public function create(array $params) {

    $transformer = ActorFactory::getActor('transformer', '${model}');
    $model = $transformer->fromArray($params);

    $this->_gatekeeper->checkCanCreate($model);

    $persister = Persister::get($model);
    $persister->create($model);
  }

  /**
   * Retrieve instances that match the given sort-paging-filtering criteria.
   *
   * @param array $spf
   */
  public function retrieve($spf) {
    if (is_object($spf)) {
      $spf = (array) $spf;
    }

    $persister = Persister::get('${model}');
    $transformer = ActorFactory::getActor('transformer', '${model}');

    $c = new Criteria();

    // Apply paging, sorting and filters to the criteria
    if (isset($spf['filter'])) {
      foreach ($spf['filter'] AS $column => $value) {
        $c->addEquals($column, $value);
      }
    }

    if (isset($spf['sort'])) {
      foreach ($spf['sort'] AS $column => $direction) {
        if ($direction === Criteria::SORT_DESC) {
          $c->addSort($column, Criteria::SORT_DESC);
        } else {
          $c->addSort($column);
        }
      }
    }

    if (isset($spf['page'])) {
      $limit = $spf['page']['limit'];
      $offset = isset($spf['page']['offset'])
        ? $spf['page']['offset']
        : null;

      $c->setLimit($limit, $offset);
    }

    // Retrieve the models that match the given spf
    $persister = ActorFactory::getActor('persister', '${model}');
    $models = $persister->retrieve($c);
    $total = $persister->count($c);

    // Check that current user has access to read the selected models
    // TODO Should this always throw an exception?  Could simply ignore models
    //      the user isn't allowed to read
    foreach ($models AS $model) {
      $this->_gatekeeper->checkCanRead($model);
    }
    
    $data = array();
    foreach ($models AS $model) {
      $data[] = $transformer->asArray($model);
    }
    return array(
      'data' => $data,
      'total' => $total
    );
  }

  /**
   * @RequestType post
   */
  public function update(array $params) {
    $transformer = ActorFactory::getActor('transformer', '${model}');
    $model = $transformer->fromArray($params);

    $persister = Persister::get('${model}');
    $original = $persister->getById($model->get${idColumn}());
    
    $this->_gatekeeper->checkCanWrite($original);

    $persister->update($model);
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

    foreach ($models AS $model) {
      $persister->delete($model);
    }
  }
}
