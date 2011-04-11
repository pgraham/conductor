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

use \conductor\model\DecoratedModel;

use \reed\generator\CodeTemplateLoader;

/**
 * Populator for the model-editor.js template.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelEditorBuilder {

  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  public function build(DecoratedModel $model) {
    $columns = array();

    foreach ($model->getProperties() AS $prop) {
      $propId = strtolower($prop->getIdentifier());

      $columns[] = array(
        'id'  => $propId,
        'lbl' => $prop->getDisplayName()
      );
    }

    $templateValues = Array
    (
      'model'        => $model->getIdentifier(),
      'idProperty'   => strtolower($model->getId()->getName()),
      'crudService'  => $model->getCrudServiceName(),
      'columns'      => $columns,
      'singular'     => $model->getDisplayName(),
      'plural'       => $model->getDisplayNamePlural()
    );

    return $this->_templateLoader->load('model-editor.js', $templateValues);
  }
}
