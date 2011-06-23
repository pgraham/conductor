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
namespace conductor\model;

use \ReflectionClass;
use \ReflectionException;

use \clarinet\model\Model;

use \reed\reflection\Annotations;

/**
 * Base class for object's that encapsulate a set of related data about a
 * model defined in a PHP interface.  The interface must be in the same
 * namespace as the model class (is in same namespace and is
 * named <model-name><decorator-suffix>) and defines methods for each of the
 * model's properties.  The methods must be named the same as the property or
 * relationship getter.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class ModelView {

  /** The model instance from which this view is built */
  protected $_model;

  /** The annotations that contain information about the model */
  protected $_modelInfo;

  /**
   * The annotations that contain information about the model's properties.
   * The annotations are indexed by property name.
   */
  protected $_propertyInfo = array();

  /**
   * The annotations that contain information about the model's relationships.
   * The annotations are indexed by relationship name.
   */
  protected $_relationshipInfo = array();

  /* Reflection class for the model */
  private $_classInfo;

  /* The suffix of the model view interface to reflect */
  private $_suffix;

  /**
   * Create a new ModelView for the given Model instance.  If provided,
   * a decorator interface will be parsed for annotations which will be
   * provided to the extending class.  Implementing classes must call this
   * constructor before accessing annotation data.
   *
   * @param Model $model
   * @param string $decoratorSuffix The suffix for the name of the interface
   *   that decorates the model with additional meta-information pertinent
   *   to this view.
   */
  protected function __construct(Model $model, $decoratorSuffix) {
    $this->_model = $model;
    $this->_suffix = $decoratorSuffix;

    try {
      $decoratorName = $model->getClass() . $this->_suffix;
      $this->_classInfo = new ReflectionClass($decoratorName);

      $this->_modelInfo = new Annotations($this->_classInfo);
    } catch (ReflectionException $swallowed) {

      // This isn't an error since defining a view interface is always
      // optional. It is the responsibility of the implementing class to
      // provide defaults if a view interface is not defined.
      $this->_modelInfo = new Annotations();
    }

    if ($this->_classInfo !== null) {
      foreach ($model->getProperties() AS $property) {
        $propId = $property->getIdentifier();
        $methodName = 'get' . $property->getName();

        $annotations = null;
        if ($this->_classInfo->hasMethod($methodName)) {
          $method = $this->_classInfo->getMethod($methodName);
          $this->_propertyInfo[$propId] = new Annotations($method);
        } else {
          $this->_propertyInfo[$propId] = new Annotations();
        }
      }

      foreach ($model->getRelationships() AS $relationship) {
        $relId = $relationship->getIdentifier();
        $methodName = 'get' . $relationship->getLhsProperty();

        $annotations = null;
        if ($this->_classInfo->hasMethod($methodName)) {
          $method = $this->_classInfo->getMethod($methodName);
          $this->_relationshipInfo[$relId] = new Annotations($method);
        } else {
          $this->_relationshipInfo[$relId] = new Annotations();
        }
      }
    }
  }
}
