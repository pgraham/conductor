<?php
namespace ${ns};

use \clarinet\ActorFactory;
use \clarinet\Clarinet;
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
  public function create($params) {
    $transformer = ActorFactory::getActor('transformer', '${model}');
    $model = $transformer->fromArray($params);
    Clarinet::save($model);
  }

  public function retrieve() {
    $c = new Criteria();
    $models = Clarinet::get('${model}', $c);
    
    $json = Array();
    foreach ($models AS $model) {
      $json[] = Clarinet::asArray($model);
    }
    return $json;
  }

  public function update() {
  }

  public function delete() {
  }
}
