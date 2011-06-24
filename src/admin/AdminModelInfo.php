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

use \conductor\modeling\ModelView;

use \reed\String;

/**
 * This class provides information about a model for generating the admin
 * client.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminModelInfo extends ModelView {

  /**
   * Constant used to display the property in both the model list and the model
   * edit form.  This is the default for properties.
   */
  const DISPLAY_BOTH = 'both';

  /**
   * Constant used to display the property in the model edit form.
   */
  const DISPLAY_EDIT = 'edit';

  /**
   * Constant used to display the property or relationship in the admin
   * interface as read-only.  This means that the property/relationship will
   * be displayed in the model's grid as a column but will not be displayed in
   * the model's edit form.
   */
  const DISPLAY_LIST = 'list';

  /**
   * Constant used to hide the property or relationship in the admin interface.
   * If a property or relationship is annotated with @Display none, then it will
   * not be displayed anywhere in the admin interface.  This is the default
   * value for relationships.
   */
  const DISPLAY_NONE = 'none';

  /**
   * The suffix used for identifying model view interfaces parsed by this class.
   */
  const VIEW_SUFFIX = 'Display';

  /* The basename of the decorated model class. */
  private $_classBaseName;

  /*
   * The file to include in the client that provides client side extensions to
   * the model.
   */
  private $_clientModel;

  /*
   * The display names for the model.  This is an array with two indexes. The
   * singular display name is contained at index 'singular' and the plural name
   * is contained at index 'plural'.
   */
  private $_displayNames;

  /*
   * The model property that contains the label to be used for individual
   * entities in a list.
   *
   * IMPORTANT: This can be a property that only exists in the client model.
   */
  private $_nameProperty;

  /* The plural display name for the model. */
  private $_plural;

  /* Array of property info objects. */
  private $_properties = array();

  /* Array of relationship info objects. */
  private $_relationships = array();

  /* The singular display name for the model. */
  private $_singular;

  /**
   * Create a new AdminModelDecorator instance for the the Model.
   *
   * @param Model $model The model for which admin client information is
   *   derived.
   */
  public function __construct(Model $model) {
    parent::__construct($model, self::VIEW_SUFFIX);

    $nameParts = explode('\\', $this->_model->getClass());
    $this->_classBaseName = array_pop($nameParts);

    $this->_parseModelInfo();
    $this->_parsePropertyInfo();
    $this->_parseRelationshipInfo();
    
    $this->_setDefaults();
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
    return $this->_displayNames['singular'];
  }

  /**
   * Getter for the model's plural display name.
   *
   * @return string
   */
  public function getDisplayNamePlural() {
    return $this->_displayNames['plural'];
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

  /**
   * Get the AdminPropertyInfo object for the property with the given
   * identifier.
   *
   * @param string $propId
   * @return AdminPropertyInfo
   */
  public function getProperty($propId) {
    return $this->_properties[$propId];
  }

  /**
   * Get the AdminRelationshipInfo object for the relationship with the given
   * identifier.
   *
   * @param string $relId
   * @return AdminRelationshipInfo
   */
  public function getRelationship($relId) {
    return $this->_relationships[$relId];
  }

  /* Parse any model information defined in the model view */
  private function _parseModelInfo() {
    $this->_displayNames = AdminViewParser::parseDisplayNames(
      $this->_classBaseName, $this->_modelInfo);

    if (isset($this->_modelInfo['labelledby'])) {
      $this->_nameProperty = $this->_modelInfo['labelledby'];
    }

    // See if a client model has been defined
    $classInfo = new ReflectionClass($this->_model->getClass());
    $clientModelFile = dirname($classInfo->getFileName())
      . "/{$this->_classBaseName}.js";
    if (file_exists($clientModelFile)) {
      $this->_clientModel = $clientModelFile;
    }
  }

  private function _parsePropertyInfo() {
    foreach ($this->_propertyInfo AS $propId => $annotations) {
      $propName = $this->_model->getProperty($propId)->getName();
      $this->_properties[$propId] = new AdminPropertyInfo($propName,
        $annotations);
    }
  }

  private function _parseRelationshipInfo() {
    foreach ($this->_relationshipInfo AS $relId => $annotations) {
      $relName = $this->_model->getRelationship($relId)->getLhsProperty();
      $this->_relationships[$relId] = new AdminRelationshipInfo($relName,
        $annotations);
    }
  }

  private function _setDefaults() {

    //
    // Default display names are determined by the AdminViewParser
    //

    // Determine default name property.  If the model has a property called name
    // then it will be used, otherwise the ID is used.
    if ($this->_nameProperty === null) {
      // The default for the name property is a property named name, if it
      // exists, or the id property
      if ($this->_model->hasProperty('Name')) {
        $this->_nameProperty = 'Name';
      } else {
        $this->_nameProperty = strtolower($this->_model->getId()->getName());
      }
    }
  }
}
