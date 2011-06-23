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

use \ArrayAccess;
use \ArrayIterator;
use \InvalidArgumentException;
use \IteratorAggregate;
use \OutOfBoundsException;

use \clarinet\model\Model;
use \clarinet\model\Parser as ModelParser;

/**
 * This class encaspulates a set of Decorated models.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelSet implements ArrayAccess, IteratorAggregate {

  /* List of encapsulated models */
  private $_models = array();

  /**
   * Create a new model set containing decoratable model representations for
   * the given set of classnames.
   *
   * @param string[] $modelNames
   */
  public function __construct(array $modelNames) {
    foreach ($modelNames AS $modelName) {
      $model = ModelParser::getModel($modelName);
      $this->_models[] = $model;
    }
  }

  /*
   * ===========================================================================
   * ArrayAccess
   * ===========================================================================
   */

  public function offsetExists($offset) {
    return isset($this->_models[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->_models[$offset]) ? $this->_models[$offset] : null;
  }

  public function offsetSet($offset, $value) {
    if ($offset !== null && !is_int($offset)) {
      throw new OutOfBoundsException("A ModelSet only supports numeric indexing");
    }

    if ($value !== null) {
      if (!is_object($value) || !($value instanceof Model)) {
        throw new InvalidArgumentException("A ModelSet only supports object"
          . " values of type clarinet\model\Model");
      }
    }

    if ($offset === null) {
      $this->_models[] = $value;
    } else {
      $this->_models[$offset] = $value;
    }
  }

  public function offsetUnset($offset) {
    unset($this->_models[$offset]);
  }

  /*
   * ===========================================================================
   * IteratorAggregate
   * ===========================================================================
   */

  public function getIterator() {
    return new ArrayIterator($this->_models);
  }
}
