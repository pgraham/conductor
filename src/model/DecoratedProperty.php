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

use \clarinet\model\Property;

/**
 * Wrapper class for a clarinet\model\Property that allows additional
 * information to be attached via a decorator.  This class supports the same
 * interface as a clarinet\model\Property.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DecoratedProperty {

  /* The property's decorators */
  private $_decorators = Array();

  /* An identifier for the property */
  private $_id;

  /* The model to which this property belongs */
  private $_model;

  /* The decorated property */
  private $_property;

  /**
   * Wrap the given property with decoration capabilities.
   *
   * @param Property $property
   */
  public function __construct(Property $property, DecoratedModel $model) {
    $this->_property = $property;
    $this->_model = $model;

    $this->_id = strtolower($property->getName());
  }

  /**
   * Magic method to delegate to the wrapped model or one of its decorators.
   *
   * @param string $name The name of the function being called.
   * @param string $args The arguments to pass to the invoked function.
   */
  public function __call($name, $args) {
    if (method_exists($this->_property, $name)) {
      return call_user_func_array(array($this->_property, $name), $args);
    }

    foreach ($this->_decorators AS $decorator) {
      if (method_exists($decorator, $name)) {
        return call_user_func_array(array($decorator, $name), $args);
      }
    }

    // If we've reached this point then neither the model nor any of its
    // decorators support the invoked method, so throw an Exception.
    throw new BadMethodCallException(
      "Property does not support nor is decorated with method $name");
  }

  /**
   * Decorate the property with information provided by the given decorator.
   * Once a decorator has been attached, the instance is extend with the
   * decorator's interface.  Any single PropertyDecorator instance can only
   * decorate one property.
   *
   * @param PropertyDecorator $decorator
   */
  public function decorate(PropertyDecorator $decorator) {
    $this->_decorators[] = $decorator;
  }

  /**
   * Getter for the property's string identifier.
   *
   * @return string An identifying string for the model represented by the view.
   */
  public function getIdentifier() {
    return $this->_id;
  }

  /**
   * Getter for the model to which the property belongs.
   *
   * @return DecoratedModel
   */
  public function getModel() {
    return $this->_model;
  }
}
