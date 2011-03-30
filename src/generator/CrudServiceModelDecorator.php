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
namespace conductor\generator;

use \clarinet\model\Model;

use \conductor\model\DecoratedProperty;
use \conductor\model\ModelDecorator;

/**
 * This class provides information about a model for generating a crud service.
 *
 * TODO - Ensure that the model has been initialized when the decorators getters
 *        are invoked.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceModelDecorator implements ModelDecorator {

  /**
   * The namespace in which generated CRUD services live.
   */
  const CRUD_SERVICE_NS = 'conductor\service\crud';

  /* The decorated model */
  private $_model;

  /*
   * The name of the model's CRUD service.  This is used as the basename of the
   * service's class name.  The service's class will live in the name space
   * defined by CRUD_SERVICE_NS.
   */
  private $_serviceName;

  /**
   * ModelDecorator implementation, nothing to do here.
   */
  public function decorateProperty(DecoratedProperty $property) {}

  /**
   * Getter for the fully qualified name of the mode's CRUD service class.
   *
   * @return string
   */
  public function getCrudServiceClass() {
    return self::CRUD_SERVICE_NS . '\\' . $this->_serviceName;
  }

  /**
   * Getter for the model's CRUD service name.  The service's class will live in
   * the name space defined by {@link #CRUD_SERVICE_NS}.
   *
   * @return string
   */
  public function getCrudServiceName() {
    return $this->_serviceName;
  }

  /**
   * Set the decorated model, can only be called once.
   *
   * @param Model $model
   */
  public function initModel(Model $model) {
    $this->_model = $model;
    $this->_serviceName = $model->getActor() . 'Crud';
  }
}
