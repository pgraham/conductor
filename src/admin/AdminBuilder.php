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

use \conductor\generator\CrudServiceInfo;
use \conductor\modeling\ModelSet;

/**
 * This class populates the conductor-admin.js template with the given model
 * info.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminBuilder {

  private $_models;

  /**
   * Create a new builder for the conductor-admin.js template.
   *
   * @param ModelInfo $modelInfo
   */
  public function __construct(ModelSet $models) {
    $this->_models = $models;
  }

  /**
   * Populate the conductor-admin.js template and return the result.
   *
   * @return string Populated conductor-admin.js
   */
  public function build() {
    $modelEditorBuilder = new ModelEditorBuilder();
    $modelFormBuilder = new ModelFormBuilder();

    $editors = Array();
    $forms = Array();
    $modelNames = Array();
    foreach ($this->_models AS $model) {
      $editors[] = $modelEditorBuilder->build($model);
      $forms[] = $modelFormBuilder->build($model);

      $modelNames[] = $model->getIdentifier();
    }

    $templateValues = Array
    (
      'models'     => $this->_buildModelJsonArray(),
      'modelNames' => $modelNames,
      'editors'    => $editors,
      'forms'      => $forms
    );

    return $templateValues;
  }

  /* Build a JSONable array for the encapsulated model set */
  private function _buildModelJsonArray() {
    $jsonable = Array();
    foreach ($this->_models AS $model) {
      $adminInfo = new AdminModelInfo($model);
      $crudInfo = new CrudServiceInfo($model);
      $modelJson = Array
      (
        'name' => Array
          (
            'singular' => $adminInfo->getDisplayName(),
            'plural'   => $adminInfo->getDisplayNamePlural()
          ),
        'crudService' => $crudInfo->getCrudServiceName()
      );

      $jsonable[$model->getIdentifier()] = $modelJson;
    }
    return $jsonable;
  }
}
