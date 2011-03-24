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

use \conductor\generator\ModelInfo;

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

  public function build(ModelInfo $model) {
    $propertyInputBuilder = new PropertyInputBuilder();

    $properties = Array();
    $inputs = Array();
    foreach ($model->getProperties() AS $prop) {
      $properties[] = strtolower($prop->getName());
      $inputs[] = $propertyInputBuilder->build($prop);
    }

    $templateValues = Array
    (
      'model'          => $model->getName(),
      'properties'     => $properties,
      'propertyInputs' => $inputs
    );

    return $this->_templateLoader->load('model-form.js', $templateValues);
  }
}
