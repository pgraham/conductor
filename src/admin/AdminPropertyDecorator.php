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

use \conductor\model\DecoratedProperty;
use \conductor\model\PropertyDecorator;

use \reed\reflection\Annotations;

/**
 * This class provides information about a model property for generating the
 * admin client.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminPropertyDecorator implements PropertyDecorator {

  /* The display name for the property */
  private $_displayName;

  public function __construct(DecoratedProperty $property,
      Annotations $annotations = null)
  {
    if (isset($annotations['display']['name'])) {
      $this->_displayName = $annotations['display']['name'];
    } else {
      $this->_displayName = ucfirst($property->getName());
    }
  }

  /**
   * Getter for the properties display label.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }
}
