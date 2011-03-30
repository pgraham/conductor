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
 * model.  This class defines model decorators.  A model decorator is an
 * interface that follows a naming convention (is in same namespace and is
 * named <model-name><decorator-suffix>) and defines methods for each of the
 * model's properties.  The methods can either be named the same as the property
 * getter or the name of the property without the get prefix.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class ModelView implements ModelDecorator {

  /** The model instance from which this view is built */
  protected $_model;

  /* Reflection class for the model */
  private $_classInfo;

  /* The suffix of the model view interface to reflect */
  private $_suffix;

  /**
   * Create a new ModelView for the given Model instance.  If provided,
   * a decorator interface will be parsed for annotations which will be
   * provided to the extending class.
   *
   * @param Model $model
   * @param string $decoratorSuffix The suffix for the name of the interface
   *   that decorates the model with additional meta-information pertinent
   *   to this view.
   */
  protected function __construct($decoratorSuffix) {
    $this->_suffix = $decoratorSuffix;
  }

  /**
   * Store property annotations and delegate.
   */
  public function decorateProperty(DecoratedProperty $property) {
    // If no view interface has been defined then there is nothing to do here
    if ($this->_classInfo === null) {
      $this->_initProperty($property, null);
      return;
    }

    $propName = $property->getName();
    $methodName = 'get' . ucfirst($propName);

    $annotations = null;
    if ($this->_classInfo->hasMethod($methodName)) {
      $method = $this->_classInfo->getMethod($methodName);
      $annotations = new Annotations($method);
    }

    $this->_initProperty($property, $annotations);
  }

  /**
   * Initialize the view with the decorated model.
   *
   * @param Model $model
   */
  public function initModel(Model $model) {
    $this->_model = $model;

    try {
      $decoratorName = $model->getClass() . $this->_suffix;
      $this->_classInfo = new ReflectionClass($decoratorName);

      $annotations = new Annotations($this->_classInfo);

    } catch (ReflectionException $swallowed) {
      // This isn't an error since defining a view interface is always
      // optional. It is the responsibility of the implementing class to
      // provide defaults if a view interface is not defined.
      $annotations = null;
    }

    $this->_init($annotations);
  }

  /**
   * This is called once the ModelView object has been attached to a
   * DecoratedModel.
   */
  protected abstract function _init(Annotations $annotations = null);

  /**
   * This is called to decorate a property.
   */
  protected abstract function _initProperty(DecoratedProperty $property,
    Annotations $annotations = null);
}
