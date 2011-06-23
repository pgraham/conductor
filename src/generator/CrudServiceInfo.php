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

use \conductor\modeling\ModelView;

/**
 * This class provides information about a model for generating a crud service.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceInfo extends ModelView {

  /**
   * The namespace in which generated CRUD services live.
   */
  const CRUD_SERVICE_NS = 'conductor\service\crud';


  /**
   * The suffix used for identifying model view interfaces parsed by this class.
   */
  const VIEW_SUFFIX = 'Crud';

  /*
   * The name of the model's CRUD service.  This is used as the basename of the
   * service's class name.  The service's class will live in the name space
   * defined by CRUD_SERVICE_NS.
   */
  private $_serviceName;

  public function __construct(Model $model) {
    parent::__construct($model, self::VIEW_SUFFIX);

    $this->_serviceName = $model->getActor() . 'Crud';
  }

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

}
