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
use \clarinet\model\Relationship;

use \conductor\generator\CrudServiceInfo;

use \reed\generator\CodeTemplateLoader;

/**
 * Populator for the model-form.js template.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelFormBuilder {

  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  public function build(Model $model) {
    $acceptedDisplayStates = array(
      AdminModelInfo::DISPLAY_BOTH,
      AdminModelInfo::DISPLAY_EDIT
    );

    $adminModelInfo = new AdminModelInfo($model);
    $crudInfo = new CrudServiceInfo($model);

    $properties = array();
    $inputs = array();
    $tabs = array();

    $relInputs = array();
    foreach ($model->getRelationships() AS $rel) {
      $modelId = $rel->getLhs()->getIdentifier();

      $relId = $rel->getIdentifier();
      $relName = $rel->getLhsProperty();
      $relInfo = $adminModelInfo->getRelationship($relId);

      $lhs = $rel->getLhs();
      $rhs = $rol->getRhs();
      $rhsInfo = new AdminModelInfo($rhs);

      if (in_array($relInfo->getDisplay(), $acceptedDisplayStates)) {

        $relInputs = array(
          'type'           => $rel->getType(),
          'name'           => "{$modelId}_{$relName}_input",
          'relationship'   => $relName,
          'label'          => $adminModelInfo->getDisplayName(),
          'lhsIdProperty'  => $lhs->getId()->getName(),
          'rhs'            => $rhs->getIdentifier(),
          'rhsIdProperty'  => $rhs->getId()->getName(),
          'rhsCrudService' => $rhs->getActor() . 'Crud',
          'rhsColumn'      => $rel->getRhsColumn(),
          'nameProperty'   => $rhsInfo->getNameProperty()
        );

        if ($rel->getType() === Relationship::TYPE_MANYTOONE) {
          $properties[] = array(
            'id' => $relName,
            'default' => 'null'
          );
        } else {
          $tabs[] = "inputs.push("
            . "{$model->getIdentifier()}_{$relName}_input(model));\n"
            . "tabs['{$relInfo->getDisplayName()}'] = "
            . "inputs[inputs.length - 1].elm;";
        }
      }
    }

    $propInputs = array();
    foreach ($model->getProperties() AS $prop) {
      $modelId = $prop->getModel()->getIdentifier();

      $propId = $prop->getIdentifier();
      $propInfo = $adminModelInfo->getProperty($propId);

      if (in_array($propInfo->getDisplay(), $acceptedDisplayStates)) {
        $properties[] = array(
          'id' => $propId,
          'default' => $prop->getDefault() !== null
            ? $prop->getDefault()
            : 'null'
        );

        $propInputs[] = array(
          'type' => $prop->getType(),
          'name' => "{$modelId}_{$propId}_input",
          'property' => $propId,
          'label'    => $propInfo->getDisplayName()
        );

      }
    }

    $templateValues = Array
    (
      'model'          => $model->getIdentifier(),
      'singular'       => $adminModelInfo->getDisplayName(),
      'properties'     => $properties,
      'numProperties'  => count($properties),
      'propInputs'     => $propInputs,
      'relInputs'      => $relInputs,
      'tabs'           => $tabs,
      'crudServiceVar' => $crudInfo->getCrudServiceName(),
      'idProperty'     => $model->getId()->getIdentifier()
    );

    return $this->_templateLoader->load('model-form.js', $templateValues);
  }
}
