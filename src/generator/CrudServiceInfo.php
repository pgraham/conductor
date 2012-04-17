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

use \reed\reflection\Annotations;
use \ReflectionClass;
use \zeptech\orm\generator\model\Model;

/**
 * This class provides information about a model for generating a crud service.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceInfo {

  /* The name of the generated service class. */
  private $_className;

  /* The display name of the model. */
  private $_displayName;

  /* The plural display name of the model. */
  private $_displayNamePlural;

  /* The model for which this class provides information. */
  private $_model;

  /*
   * This is the name of the javascript variable that is used for the service's
   * proxy.  This can be specified as the value of this @ProxyName annotation at
   * the class level of the Model interface.
   */
  private $_proxyName;

  public function __construct(Model $model) {
    $this->_model = $model;

    $actor = $model->getActor();
    $this->_className = "zeptech\\dynamic\\crud\\$actor";

    $this->_proxyName = $model->getProxyName();
    if ($this->_proxyName === null) {
      // For the default, strip any namespace component from the actor and
      // append 'Crud' to the result
      $actor = $model->getActor();
      $proxy = substr($actor, strrpos($actor, '_') + 1) . 'Crud';
      $this->_proxyName = $proxy;
    }

    $this->_displayName = $model->getDisplayName();
    if ($this->_displayName === null) {
      $actorParts = explode('_', $model->getActor());
      $this->_displayName = array_pop($actorParts);
    }

    $this->_displayNamePlural = $model->getDisplayNamePlural();
    if ($this->_displayNamePlural === null) {
      $this->_displayNamePlural = $this->_displayName . 's';
    }
  }

  /**
   * Getter for the name of the generated service class.
   *
   * @return string
   */
  public function getClassName() {
    return $this->_className;
  }

  /** 
   * Getter for the display name of the model on which this crud service will
   * act.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }

  /**
   * Getter for the display name of the model to use in situations where a
   * plurality is necessary.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNamePlural;
  }

  /**
   * Getter for the model for which this class provides information.
   *
   * @return Model $model
   */
  public function getModel() {
    return $this->_model;
  }

  /**
   * Getter for the name of the Javascript variable to use as the proxy.
   * This variable will be put into the global namespace.  This can be specified
   * in the model using the @ProxyName annotation at the class level. If not
   * specified then this defaults the service name.
   *
   * @return string
   */
  public function getProxyName() {
    return $this->_proxyName;
  }

}
