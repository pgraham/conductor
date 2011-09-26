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
   * This is the name of the javascript variable that is used for the service's
   * proxy.  This can be specified as the value of this @ProxyName annotation at
   * the class level of the ModelView interface.
   *
   * TODO The proxy name needs to be made configurable in bassoon before this
   *      can be implemented. 
   */
  private $_proxyName;

  /*
   * The name of the model's CRUD service.  This is used as the basename of the
   * service's class name.  The service's class will live in the name space
   * defined by CRUD_SERVICE_NS.
   */
  private $_serviceName;

  public function __construct(Model $model) {
    parent::__construct($model, self::VIEW_SUFFIX);

    $this->_serviceName = $model->getActor() . 'Crud';

    $this->_proxyName = isset($this->_modelInfo['proxyname'])
      ? $this->_modelInfo['proxyname']
      : $this->_serviceName;
  }

  /**
   * NOT YET IMPLEMENTED
   *
   * Getter for the name of the Javascript variable to use as the proxy.
   * This variable will be put into the global namespace.  This can be specified
   * in the a model's CRUD interface using the @ProxyName annotations. If not
   * specified then this defaults the service name.
   *
   * @return string
   */
  public function getProxyName() {
    return $this->_proxyName;
  }

  /**
   * Getter for the fully qualified name of the model's CRUD service class.
   *
   * @return string
   */
  public function getServiceClass() {
    return self::CRUD_SERVICE_NS . '\\' . $this->_serviceName;
  }

  /**
   * Getter for the model's CRUD service name.  The service's class will live in
   * the name space defined by {@link #CRUD_SERVICE_NS}.
   *
   * @return string
   */
  public function getServiceName() {
    return $this->_serviceName;
  }

}
