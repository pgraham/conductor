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

use \ReflectionClass;

use \clarinet\model\Info;

use \reed\util\ReflectionHelper;

/**
 * This class encapsulates information about a model for use by various
 * generators.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelInfo {

  /**
   * The namespace in which generated CRUD services live.
   */
  const CRUD_SERVICE_NS = 'conductor\service\crud';

  /* The model's class name */
  private $_className;

  /* The display name for the model */
  private $_displayName;

  /* The display name for the model in a plural context */
  private $_displayNamePlural;

  /* The name used to identify the model in javascript code */
  private $_name;

  /*
   * The name of the model's CRUD service.  This is used as the name of the
   * client side variable that contains the service's proxy methods as well as
   * the basename of the service's class name.  The service's class will live
   * in the name space defined in a constant in this class.
   */
  private $_serviceName;

  /* The model's properties' display names */
  private $_propertyDisplayNames;

  /**
   * Create a new ModelInfo object.
   *
   * @param string $modelName The represented model's class name.
   */
  public function __construct($modelName) {
    $this->_className = $modelName;

    $classInfo = new ReflectionClass($modelName);
    $docComment = $classInfo->getDocComment();
    $annotations = ReflectionHelper::getAnnotations($docComment);

    $modelNameParts = explode('\\', $modelName);
    $this->_displayName = array_pop($modelNameParts);
    $this->_name = strtolower($this->_displayName);

    if (isset($annotations['display']['name'])) {
      $this->_displayName = $annotations['display']['name'];
    }

    $this->_displayNamePlural = $this->_displayName . 's';
    if (isset($annotations['display']['plural'])) {
      $this->_displayNamePlural = $annotations['display']['plural'];
    }

    $info = new Info($this->_className);
    $this->_serviceName = $info->getActor() . 'Crud';

    $this->_properties = $info->getProperties();
  }

  /**
   * Getter for the mode's class name.
   *
   * @return string
   */
  public function getClassName() {
    return $this->_className;
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
   * Getter for the model's CRUD service.
   *
   * @return string
   */
  public function getCrudServiceName() {
    return $this->_serviceName;
  }

  /**
   * Getter for the model's sigular display name.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }

  /**
   * Getter for the model's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNamePlural;
  }

  /**
   * Getter for the model's identifier name.
   *
   * @return string
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Getter for the model's properties.
   *
   * @return clarinet\model\Property[]
   */
  public function getProperties() {
    return $this->_properties;
  }
}
