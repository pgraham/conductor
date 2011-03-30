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

use \clarinet\model\Model;

use \conductor\model\DecoratedProperty;
use \conductor\model\ModelView;

use \reed\reflection\Annotations;

/**
 * This class provides information about a model for generating the admin
 * client.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminModelDecorator extends ModelView {

  /* The display name for the model */
  private $_displayName;

  /* The display name for the model in a plural context */
  private $_displayNamePlural;

  /**
   * Create a new AdminModelDecorator instance for the the Model.
   *
   * @param Model $model The model for which admin client information is
   *   derived.
   */
  public function __construct() {
    parent::__construct('Display');
  }

  /**
   * Getter for the model's sigular display name.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }

  /**
   * Getter for the model's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNamePlural;
  }

  protected function _init(Annotations $annotations = null) {
    if (isset($annotations['display']['name'])) {
      $this->_displayName = $annotations['display']['name'];
    } else {
      // The default display name is the basename of the model's class
      $nameParts = explode('\\', $this->_model->getClass());
      $this->_displayName = ucfirst(array_pop($nameParts));
    }

    if (isset($annotations['display']['plural'])) {
      $this->_displayNamePlural = $annotations['display']['plural'];
    } else {
      // The default plural display name is the singular display name with an
      // 's' appended to the end
      $this->_displayNamePlural = $this->_displayName . 's';
    }
  }

  protected function _initProperty(DecoratedProperty $property,
      Annotations $annotations = null)
  {
    $property->decorate(new AdminPropertyDecorator($property, $annotations));
  }
}
