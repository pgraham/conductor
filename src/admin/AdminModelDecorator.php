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

use \ReflectionClass;

use \clarinet\model\Model;

use \conductor\model\DecoratedProperty;
use \conductor\model\DecoratedRelationship;
use \conductor\model\ModelView;

use \reed\reflection\Annotations;

/**
 * This class provides information about a model for generating the admin
 * client.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminModelDecorator extends ModelView {

  /**
   * Constant used to display the property or relationship as editable.  This
   * means that an input for the property/relationship will be generated and
   * display in the model's grid as a column as well as in the model's edit
   * form.  This is the default value for properties.
   */
  const DISPLAY_EDIT = 'edit';

  /**
   * Constant used to hide the property or relationship in the admin interface.
   * If a property or relationship is annotated with @Display none, then it will
   * not be displayed anywhere in the admin interface.  This is the default
   * value for relationships.
   */
  const DISPLAY_NONE = 'none';

  /**
   * Constant used to display the property or relationship in the admin
   * interface as read-only.  This means that the property/relationship will
   * be displayed in the model's grid as a column but will not be displayed in
   * the model's edit form.
   */
  const DISPLAY_READONLY = 'read-only';

  /*
   * The file to include in the client that provides client side extensions to
   * the model.
   */
  private $_clientModel;

  /* The display name for the model */
  private $_displayName;

  /* The display name for the model in a plural context */
  private $_displayNamePlural;

  /*
   * The model property that contains the label to be used for individual
   * entities in a list.
   */
  private $_nameProperty;

  /**
   * Create a new AdminModelDecorator instance for the the Model.
   *
   * @param Model $model The model for which admin client information is
   *   derived.
   */
  public function __construct() {
    parent::__construct('Display');
  }

  /**
   * Getter for the path to the file that contains client side model extensions.
   * If one is not defined this will return null.
   *
   * @return string
   */
  public function getClientModel() {
    return $this->_clientModel;
  }

  /**
   * Getter for the model's sigular display name.
   *
   * @return string
   */
  public function getDisplayName() {
    return $this->_displayName;
  }

  /**
   * Getter for the model's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNamePlural;
  }

  /**
   * Getter for the model's name property.  This is specified by the @LabelledBy
   * annotations. Default is 'Name' if the model has a property called name
   * the model's id property if it does not.
   *
   * @return string
   */
  public function getNameProperty() {
    return $this->_nameProperty;
  }

  protected function _init(Annotations $annotations = null) {
    if (isset($annotations['display'])) {
      if (isset($annotations['display']['name'])) {
        $this->_displayName = $annotations['display']['name'];
      }

      if (isset($annotations['display']['plural'])) {
        $this->_displayNamePlural = $annotations['display']['plural'];
      }
    }

    if (isset($annotations['labelledby'])) {
      $this->_nameProperty = $annotations['labelledby'];
    }

    // We'll need this in a couple of spots
    $nameParts = explode('\\', $this->_model->getClass());
    $classBaseName = array_pop($nameParts);

    // Set defaults if necessary
    if ($this->_displayName === null) {
      // The default display name is the basename of the model's class
      $this->_displayName = ucfirst($classBaseName);
    }
    if ($this->_displayNamePlural === null) {
      // The default plural display name is the singular display name with an
      // 's' appended to the end
      $this->_displayNamePlural = $this->_displayName . 's';
    }
    if ($this->_nameProperty === null) {
      // The default for the name property is a property named name, if it
      // exists, or the id property
      if ($this->_model->hasProperty('Name')) {
        $this->_nameProperty = 'name';
      } else {
        $this->_nameProperty = strtolower($this->_model->getId()->getName());
      }
    }

    // See if a client model has been defined
    $classInfo = new ReflectionClass($this->_model->getClass());
    $clientModelFile = dirname($classInfo->getFileName())
      . "/$classBaseName.js";
    if (file_exists($clientModelFile)) {
      $this->_clientModel = $clientModelFile;
    }
  }

  protected function _initProperty(DecoratedProperty $property,
      Annotations $annotations = null)
  {
    $property->decorate(new AdminPropertyDecorator($property, $annotations));
  }

  protected function _initRelationship(DecoratedRelationship $relationship,
      Annotations $annotations = null)
  {
    $relationship->decorate(new AdminRelationshipDecorator($relationship,
      $annotations));
  }
}
