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

use \conductor\model\DecoratedModel;
use \conductor\model\DecoratedRelationship;
use \conductor\Exception;

use \reed\generator\CodeTemplateLoader;

/**
 * This class builds the code for a relationship input.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class RelationshipInputBuilder {

  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  public function build(Relationship $relationship) {
    $type = $relationship->getType();
    switch ($type) {
      case Relationship::TYPE_MANYTOMANY:
      $template = 'property-input-many-to-many.js';
      break;

      case Relationship::TYPE_MANYTOONE:
      $template = 'property-input-many-to-one.js';
      break;

      case Relationship::TYPE_ONETOMANY:
      $template = 'property-input-one-to-many.js';
      break;

      default:
      assert("false /* Unrecognized relationship type: $type */");
      return '';
    }

    return $this->_templateLoader->load($template,
      $this->_getTemplateValues($relationship));
  }

  private function _getTemplateValues($relationship) {
    $lhs = $relationship->getLhs();
    $lhsInfo = new AdminModelInfo($lhs);

    $rhs = $relationship->getRhs();
    $rhsInfo = new AdminModelInfo($rhs);
    //$rhsCrud = new CrudServiceInfo($rhs);

    $values = array(
      // The identifier for the model to which the relationship belongs
      'model'         => $lhs->getIdentifier(),

      // The identifier for the model on the right side of the relationship
      'rhs'           => $rhs->getIdentifier(),

      // The name of the relationship as defined by the model's getter
      'relationship'  => $relationship->getLhsProperty(),

      // The left side's id property
      'lhsIdProperty' => $lhs->getId()->getName(),

      // The right side's ID property
      'rhsIdProperty' => $rhs->getId()->getName(),

      // The right side's Crud service proxy
      'rhsCrudService' => $rhs->getActor() . 'Crud',

      // The label to use for the relationship
      'label'         => $lhsInfo->getDisplayName(),

      // The client-side model property to use to label entities on the
      // right side of the relationship
      'nameProperty'  => $rhsInfo->getNameProperty()
    );

    if ($relationship->getType() === Relationship::TYPE_ONETOMANY) {
      $values['rhsColumn'] = $relationship->getRhsColumn();
    }
    return $values;
  }
}
