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

use \clarinet\model\Relationship;

use \conductor\model\DecoratedRelationship;
use \conductor\model\RelationshipDecorator;

use \reed\reflection\Annotations;

/**
 * This class provides information about a model relationship for generating the
 * admin client.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminRelationshipDecorator implements RelationshipDecorator {

  /* The display value for the relationship */
  private $_display;

  /*
   * The display name for the relationship. This is only relevant if
   * the relationship has an @Display annotation defined to something
   * other than 'none' in the ModelView interface.
   */
  private $_displayName;

  /**
   * Create a new AdminRelationshipDecorator for the given relationship.
   *
   * @param DecoratedRelationship $relationship The relationship to decorate.
   * @param Annotations $annotations If a view interface is defined and has
   *   a declaration for this relationship, then this will be the annotations
   *   contained in the declaration.
   */
  public function __construct(DecoratedRelationship $relationship,
      Annotations $annotations = null)
  {
    if (isset($annotations['display'])) {
      if (is_array($annotations['display'])) {
        if (isset($annotations['display']['mode'])) {
          $this->_display = $annotations['display']['mode'];
        }

        if (isset($annotations['display']['name'])) {
          $this->_displayName = $annotations['display']['name'];
        }
      } else {
        $this->_display = $annotations['display'];
      }
    }

    // Set defaults if necessary
    if ($this->_display === null) {
      switch ($relationship->getType()) {
        case Relationship::TYPE_MANYTOONE:
        $this->_display = AdminModelDecorator::DISPLAY_EDIT;
        break;

        default:
        $this->_display = AdminModelDecorator::DISPLAY_NONE;
      }
    }
    if ($this->_displayName === null) {
      $this->_displayName = ucfirst($relationship->getLhsProperty());
    }

    // TODO Validate display value
  }

  /** 
   * Getter for the relationship's display value.  Can be any
   * of the AdminModelDecorator's DISPLAY_* constants.  Default
   * is DISPLAY_NONE.
   *
   * @return string One of the the AdminModelDecorator's DISPLAY_* constants.
   */
  public function getDisplay() {
    return $this->_display;
  }

  /**
   * Getter for the relationship's display label.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }
}
