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

use \clarinet\model\Model;

/**
 * Interface for objects that decorate a DecoratedModel.  A ModelDecorator
 * instance has a one-to-one relationship with the model it is decorating.  This
 * is enforced though the {@link #initModel(Model)} method.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface ModelDecorator {

  /**
   * This method is responsible for decorating a property of the decorated model
   * with zero or more decorators.  Implementations are not required to
   * decorate properties, this is simply provided as a means of enabling better
   * encapsulation for decorators that also provide additional information for
   * properties.
   *
   * @param DecoratedProperty $property
   */
  public function decorateProperty(DecoratedProperty $property);

  /**
   * This method is responsible for the creating the one-to-one relationship
   * with the decorated Model instance.
   *
   * @param Model $model The model being decorated.
   * @throws RuntimeException if the decorator's model has already been set.
   */
  public function initModel(Model $model);
}
