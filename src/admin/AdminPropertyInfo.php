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

use \reed\reflection\Annotations;

/**
 * This class encapsulates information about a model property pertinent to
 * generating the admin interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminPropertyInfo {

  /* This display state of the property */
  private $_display;

  /* The display names for the property */
  private $_displayNames;

  /**
   * Create a new property info object for the given name and annotations.
   *
   * @param string $name
   * @param Annotations $annotations
   */
  public function __construct($name, Annotations $annotations) {
    $this->_displayNames = AdminViewParser::parseDisplayNames($name,
      $annotations);

    $this->_display = AdminViewParser::parseDisplayState($name, $annotations,
      AdminModelInfo::DISPLAY_BOTH);
  }

  /**
   * Getter for the property's display state.
   *
   * @return string One of the {@link AdminModelInfo}::DISPLAY_* constants.
   */
  public function getDisplay() {
    return $this->_display;
  }

  /**
   * Getter for the property's singular display name.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayNames['singular'];
  }

  /**
   * Getter for the property's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNames['plural'];
  }
}
