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
 * This class encapsulates information about a model relationship pertinent to
 * generating the admin interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminRelationshipInfo {

  /* The display state of the relationship. */
  private $_display;

  /* The display names for the relationship */
  private $_displayNames;

  /**
   * Create a new relationship info object for the given name and annotations.
   *
   * @param string $name
   * @param Annotations $annotations
   */
  public function __construct($name, Annotations $annotations) {
    $this->_displayNames = AdminViewParser::parseDisplayNames($name,
      $annotations);

    $this->_display = AdminViewParser::parseDisplayState($name, $annotations,
      AdminModelInfo::DISPLAY_NONE);
  }

  /**
   * Getter for the relationship's display state.
   *
   * @return string One of the {@link AdminModelInfo}::DISPLAY_* constants.
   */
  public function getDisplay() {
    return $this->_display;
  }

  /**
   * Getter for the relationship's singular display name.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayNames['singular'];
  }

  /**
   * Getter for the relationship's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNames['plural'];
  }

}
