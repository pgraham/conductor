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

use \conductor\generator\CrudServiceInfo;

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

  public function build(Model $model) {
    $acceptedDisplayStates = array(
      AdminModelInfo::DISPLAY_BOTH,
      AdminModelInfo::DISPLAY_LIST
    );

    $adminModelInfo = new AdminModelInfo($model);

    $columns = array();

    foreach ($model->getProperties() AS $prop) {
      $propId = $prop->getIdentifier();
      $propInfo = $adminModelInfo->getProperty($propId);

      if (in_array($propInfo->getDisplay(), $acceptedDisplayStates)) {

        $columns[] = array(
          'id'   => $propId,
          'type' => $prop->getType(),
          'lbl'  => $propInfo->getDisplayName()
        );
      }
    }

    foreach ($model->getRelationships() AS $rel) {
      $relId = $rel->getIdentifier();
      $relName = $rel->getLhsProperty();
      $relInfo = $adminModelInfo->getRelationship($relId);

      if (in_array($relInfo->getDisplay(), $acceptedDisplayStates)) {

        $columns[] = array(
          'id'   => $relName,
          'type' => 'object',
          'lbl'  => $relInfo->getDisplayName()
        );
      }
    }

    $crudInfo = new CrudServiceInfo($model);

    $buttons = array( 'new', 'edit', 'delete' );
    $templateValues = Array
    (
      'model'       => $model->getIdentifier(),
      'idProperty'  => $model->getId()->getIdentifier(),
      'crudService' => $crudInfo->getServiceName(),
      'columns'     => $columns,
      'singular'    => $adminModelInfo->getDisplayName(),
      'plural'      => $adminModelInfo->getDisplayNamePlural(),
      'buttons'     => $buttons
    );

    return $this->_templateLoader->load('model-editor.js', $templateValues);
  }
}
