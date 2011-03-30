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

/**
 * Instances of this interface are responsible for creating
 * {@link ModelDecorator}s of a specified type.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface ModelDecoratorFactory {

  /**
   * Create and return a new ModelDecorator instance of the type defined by
   * {@link #getDecoratorType()}.
   *
   * @return ModelDecorator
   */
  public function getDecorator();
}
