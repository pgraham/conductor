<?php
/**
 * This is a generated class - DO NOT EDIT.
 */
namespace ${actorNs};

use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \zpt\cdt\di\Injector;
use \zpt\cdt\rest\BeanRequestHandler;

class ${actorClass} extends BaseRequestHandler implements BeanRequestHandler {

  private $_service;
  private $_mappings;

  public function __construct() {
    $this->_mappings = ${php:mappings};
  }

  public function getMappings() {
    return $this->_mappings;
  }

  public function setService($service) {
    $this->_service = $service;
  }

  ${if:deleteMethods}
    public function delete(Request $request, Response $response) {
      $mappingId = $request->getMappingId();
      switch ($mappingId) {

        ${each:deleteMethods as method}
          case '${method}':
          $this->_service->${method}($request, $response);
          return;

        ${done}
      }
      parent::delete($request, $response);
    }
  ${fi}

  ${if:getMethods}
    public function get(Request $request, Response $response) {
      $mappingId = $request->getMappingId();
      switch ($mappingId) {

        ${each:getMethods as method}
          case '${method}':
          $this->_service->${method}($request, $response);
          return;

        ${done}
      }
      parent::get($request, $response);
    }
  ${fi}

  ${if:postMethods}
    public function post(Request $request, Response $response) {
      $mappingId = $request->getMappingId();
      switch ($mappingId) {

        ${each:postMethods as method}
          case '${method}':
          $this->_service->${method}($request, $response);
          return;

        ${done}
      }
      parent::post($request, $response);
    }
  ${fi}

  ${if:putMethods}
    public function put(Request $request, Response $response) {
      $mappingId = $request->getMappingId();
      switch ($mappingId) {

        ${each:putMethods as method}
          case '${method}':
          $this->_service->${method}($request, $response);
          return;

        ${done}
      }
      parent::put($request, $response);
    }
  ${fi}
}
