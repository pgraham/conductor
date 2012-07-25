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

class ${actorClass} extends BaseRequestHandler implements RequestHandler {

  private $_srvc;

  public function __construct() {
    $this->_srvc = new \${serviceClass}();
    ${if:beans}
      Injector::inject($this->_srvc, ${php:beans});
    ${fi}
  }

  ${if:deleteMethods}
    public function delete(Request $request, Response $response) {
      $mappingId = $request->getMappingId();
      switch ($mappingId) {

      ${each:deleteMethods as method}
          case '${method[name]}':
          $this->_srvc->${method[name]}($request, $response);
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
          case '${method[name]}':
          $this->_srvc->${method[name]}($request, $response);
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
          case '${method[name]}':
          $this->_srvc->${method[name]}($request, $response);
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
          case '${method[name]}':
          $this->_srvc->${method[name]}($request, $response);
          return;

      ${done}
      }
      parent::put($request, $response);
    }
  ${fi}
}
