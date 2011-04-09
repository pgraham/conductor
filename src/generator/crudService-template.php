<?php
namespace ${ns};

use \clarinet\ActorFactory;
use \clarinet\Criteria;

use \conductor\Conductor;

/**
 * This is CRUD service class for a ${modelName} class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class use for generation then regenerate this class.
 *
 * @Service( name = ${className} )
 * @CsrfToken conductorsessid
 * @Requires ${autoloader}
 */
class ${className} {

  public function __construct() {
    // Ensure that conductor is initialized
    Conductor::init();
  }

  /**
   * @RequestType post
   */
  public function create(array $params) {
    $transformer = ActorFactory::getActor('transformer', '${model}');
    $model = $transformer->fromArray($params);

    $persister = ActorFactory::getActor('persister', '${model}');
    $persister->create($model);
  }

  public function retrieve() {
    $persister = ActorFactory::getActor('persister', '${model}');
    $transformer = ActorFactory::getActor('transformer', '${model}');

    $c = new Criteria();
    $models = $persister->retrieve($c);
    
    $json = array();
    foreach ($models AS $model) {
      $json[] = $transformer->asArray($model);
    }
    return $json;
  }

  /**
   * @RequestType post
   */
  public function update(array $params) {
    $transformer = ActorFactory::getActor('transformer', '${model}');
    $model = $transformer->fromArray($params);

    $persister = ActorFactory::getActor('persister', '${model}');
    $persister->update($model);
  }

  /**
   * @RequestType post
   */
  public function delete(array $ids) {
    $persister = ActorFactory::getActor('persister', '${model}');
    foreach ($ids AS $id) {
      $model = $persister->getById($id);
      $persister->delete($model);
    }
  }
}
