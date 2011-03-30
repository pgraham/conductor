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
namespace conductor\admin;

use \conductor\model\ModelDecoratorFactory;

/**
 * Factory class for {@link AdminModelDecorator}s.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminModelDecoratorFactory implements ModelDecoratorFactory {

  /**
   * Creates and returns an uninitialized {@link AdminModelDecorator}.
   *
   * @return AdminModelDecorator
   */
  public function getDecorator() {
    return new AdminModelDecorator();
  }
}
