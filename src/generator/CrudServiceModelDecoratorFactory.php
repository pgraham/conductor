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

use \conductor\model\ModelDecoratorFactory;

/**
 * Factory class for {@link CrudServiceModelDecorator}s.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceModelDecoratorFactory implements ModelDecoratorFactory {

  /**
   * Creates and returns an uninitialized {@link CrudServiceModelDecorator}.
   *
   * @return CrudServiceModelDecorator
   */
  public function getDecorator() {
    return new CrudServiceModelDecorator();
  }
}
