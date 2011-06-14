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

  public function build(DecoratedModel $model) {
    $propertyInputBuilder = new PropertyInputBuilder();

    $properties = array();
    $inputs = array();
    $tabs = array();

    $relationshipInputBuilder = new RelationshipInputBuilder();
    foreach ($model->getRelationships() AS $rel) {
      if ($rel->getDisplay() === AdminModelDecorator::DISPLAY_EDIT) {
        $propId = strtolower($rel->getLhsProperty());

        $inputs[] = $relationshipInputBuilder->build($rel);

        if ($rel->getType() === Relationship::TYPE_MANYTOONE) {
          $properties[] = array(
            'id' => $propId,
            'default' => 'null'
          );
        } else {
          $tabs = "inputs.push("
            . "{$model->getIdentifier()}_{$propId}_input(model));\n"
            . "tabs['{$rel->getDisplayName()}'] = "
            . "inputs[inputs.length - 1].elm;";
        }
      }
    }

    foreach ($model->getProperties() AS $prop) {
      $propId = strtolower($prop->getIdentifier());

      $properties[] = array(
        'id' => $propId,
        'default' => $prop->getDefault() !== null
          ? $prop->getDefault()
          : 'null'
      );
      $inputs[] = $propertyInputBuilder->build($prop);
    }

    $templateValues = Array
    (
      'model'          => $model->getIdentifier(),
      'singular'       => $model->getDisplayName(),
      'properties'     => $properties,
      'numProperties'  => count($properties),
      'inputs'         => $inputs,
      'tabs'           => $tabs,
      'crudServiceVar' => $model->getCrudServiceName(),
      'idProperty'     => strtolower($model->getId()->getName())
    );

    return $this->_templateLoader->load('model-form.js', $templateValues);
  }
}
