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

use \reed\util\ReflectionHelper;

/**
 * This class encapsulates information about a model for use by various
 * generators.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelInfo {

  /* The display name for the model */
  private $_displayName;

  /* The display name for the model in a plural context */
  private $_displayNamePlural;

  /* The name of the model's CRUD service */
  private $_crudServiceName;

  /**
   * Create a new ModelInfo object.
   *
   * @param string $modelName The represented model's class name.
   */
  public function __construct($modelName) {
    $classInfo = new ReflectionClass($modelName);
    $docComment = $classInfo->getDocComment();
    $annotations = ReflectionHelper::getAnnotations($docComment);

    $modelNameParts = explode('\\', $modelName);
    $this->_displayName = array_pop($modelNameParts);

    if (isset($annotations['display']['name'])) {
      $this->_displayName = $annotations['display']['name'];
    }

    $this->_displayNamePlural = $this->_displayName . 's';
    if (isset($annotations['display']['plural'])) {
      $this->_displayNamePlural = $annotations['display']['plural'];
    }

    $this->_serviceName = str_replace('\\', '_', $modelName) . 'CRUD';
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
}
