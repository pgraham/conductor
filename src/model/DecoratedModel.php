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

use \BadMethodCallException;

use \clarinet\model\Model;

/**
 * Wrapper class for a clarinet\model\Model that allows additional information
 * to be attached via a decorator.  This class supports the same interface as a
 * clarinet\model\Model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DecoratedModel {

  /* The decorated model */
  private $_model;

  /* The model's decorators */
  private $_decorators = Array();

  /* A string identifier for the model */
  private $_id;

  /* Decorated model properties */
  private $_properties = Array();

  /**
   * Wrap with given model with decoration capabilities.
   *
   * @param Model $model
   */
  public function __construct(Model $model) {
    $this->_model = $model;
    $this->_id = strtolower($model->getActor());

    foreach ($model->getProperties() AS $property) {
      $this->_properties[] = new DecoratedProperty($property, $this);
    }
  }

  /**
   * Magic method to delegate to the wrapped model or one of its decorators.
   *
   * @param string $name The name of the function being called
   * @param array $args The arguments to pass to the invoked function.
   */
  public function __call($name, $args) {
    if (method_exists($this->_model, $name)) {
      return call_user_func_array(array($this->_model, $name), $args);
    }

    foreach ($this->_decorators AS $decorator) {
      if (method_exists($decorator, $name)) {
        return call_user_func_array(array($decorator, $name), $args);
      }
    }

    // If we've reached this point then neither the model nor any of its
    // decorators support the invoked method, so throw an Exception
    throw new BadMethodCallException(
      "Model is not decorated with method $name");
  }

  /**
   * Decorate the model with information provided by the given decorator.
   * Once a decorator has been attached, the instance is extended with the
   * decorator's interface.  Any single ModelDecorator instance can only
   * decorate one model.
   *
   * @param ModelDecorator $decorator
   */
  public function decorate(ModelDecorator $decorator) {
    $decorator->initModel($this->_model);
    $this->_decorators[] = $decorator;

    foreach ($this->_properties AS $property) {
      $decorator->decorateProperty($property);
    }
  }

  /**
   * Add a Property decorator to the model's properties.
   *
   * @param PropertyDecorator $decorator
   */
  public function decorateProperty(PropertyDecorator $decorator) {
    foreach ($this->_properties AS $property) {
      $property->decorate($decorator);
    }
  }

  /**
   * Getter for the model's string identifier.
   *
   * @return string An identifying string for the model represented by the view.
   */
  public function getIdentifier() {
    return $this->_id;
  }

  /**
   * Getter for the model's properties.
   *
   * @return DecoratedProperty[]
   */
  public function getProperties() {
    return $this->_properties;
  }
}
