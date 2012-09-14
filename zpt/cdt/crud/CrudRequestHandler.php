<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\crud;

use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \zpt\cdt\i18n\ModelMessages;
use \StdClass;

/**
 * This class dispatches CRUD operations for models to the appropriate CRUD
 * service class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudRequestHandler extends BaseRequestHandler implements RequestHandler {

  private $_crud;
  private $_info;
  private $_modelName;

  public function __construct($modelName) {
    $this->_modelName = $modelName;
  }

  /**
   * @Override
   */
  public function delete(Request $request, Response $response) {
    $this->_ensureCrud();

    $id = $request->getParameter('id');
    if ($id === null) {
      $response->header('HTTP/1.1 405 Method Not Allowed');
      $response->header('Allow: GET POST');
      $response->setData("Please specify the ID of the entity to delete.");
      return;
    }

    try {
      $this->_crud->delete(array($id));
    } catch (CrudException $e) {
      $response->header($e->getResponseHeader());
      $response->setData($e->getResponseMessage());
    }
  }

  /**
   * @Override
   */
  public function get(Request $request, Response $response) {
    $this->_ensureCrud();

    $id = $request->getParameter('id');
    if ($id !== null) {
      // Retrieve a single entity
      try {
        $response->setData($this->_crud->retrieveOne($id));
      } catch (CrudException $e) {
        $response->header($e->getResponseHeader());
        $response->setData($e->getResponseMessage());
      }
      
    } else {
      // Retrieve a list of entities
      $query = $request->getQuery();

      $spf = isset($query['spf'])
        ? json_decode($query['spf'])
        : new StdClass();

      try {
        $response->setData($this->_crud->retrieve($spf));
      } catch (CrudException $e) {
        $this->_handleException($e, $response, 'retrieving', true /* Plural */);
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
      }
    }
  }

  /**
   * @Override
   */
  public function post(Request $request, Response $response) {
    $this->_ensureCrud();

    try {
      $data = (array) $request->getData();

      $id = $request->getParameter('id');
      if ($id !== null) {
        $this->_crud->update($id, $data);
        $msg = $this->_info->updateSuccessMsg();
      } else {
        $this->_crud->create($data);
        $msg = $this->_info->createSuccessMsg();
      }
      $response->setData(array( 'msg' => array(
        'text' => $msg,
        'type' => 'info'
      )));
    } catch (CrudException $e) {
      $this->_handleException($e, $response, 'creating');
    }
  }

  /**
   * @Override
   */
  public function put(Request $request, Response $response) {
    $this->_ensureCrud();

    $id = $request->getParameter('id');
    if ($id === null) {
      $response->header('HTTP/1.1 405 Method Not Allowed');
      $response->header('Allow: GET POST');
      $response->setData("To create a new entity please use the POST method.");
      return;
    }

    try {
      $data = (array) $request->getData();

      // TODO What is allowed to be PUT?
    } catch (CrudException $e) {
      $response->header($e->getResponseHeader());
      $response->setData($e->getResponseMessage());
    }

  }

  private function _ensureCrud() {
    if ($this->_crud === null) {
      $this->_crud = CrudService::get($this->_modelName);
      $this->_info = ModelMessages::get($this->_modelName);
    }
  }

  private function _handleException(CrudException $e, Response $response,
      $action, $plural = false)
  {
    $hdr = $e->getResponseHeader();
    $msg = $e->getResponseMessage();

    // Override the default header and message if a specific cause is
    // determinable
    if ($e->isDuplicate()) {
      $msg = $this->_info->duplicateMsg($msg['field'], $msg['value']);

    } else if ($e->isInvalidFilter()) {
      $msg = $this->_info->invalidFilterMsg($msg['filter']);

    } else if ($e->isInvalidSort()) {
      $msg = $this->_info->invalidSortMsg($msg['sort']);

    } else if ($e->isNotNullViolation()) {
      $msg = $this->_info->notNullMsg($msg['field']);

    } else if (is_array($msg)) {
      $msg = array(
        'msg' => $this->_info->invalidEntityMsg(),
        'msgs' => $msg
      );

    } else if ($msg === null) {
      $msg = $this->_info->genericErrorMsg($action, $plural);
    }

    $response->header($hdr);
    $response->setData($msg);
  }

}
