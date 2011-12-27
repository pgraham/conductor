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
namespace conductor\config;

/**
 * This class encapsulates site configuration information about a specific model
 * class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelConfig {

  /* The name of the class that encapsulates the model's information. */
  private $_modelName;

  /*
   * Whether or not the model is included in the admin interface.  Models are
   * included in the admin interface by default.  To remove a model from the
   * admin interface set admin="false" in the model's declaration in
   * conductor.cfg.xml.  This will override any @Display annotations in the
   * model's view for the admin interface, if defined.
   */
  private $_hasAdmin = true;

  /*
   * Whether or not to generate a CRUD interface for the model.  True by
   * default, set crud="false" in the model's declaration in conductor.cfg.xml.
   * This will only have an effect if the model is not part of the admin
   * interface as the admin interface relies on CRUD services for the models
   * being present.  I.e <model class="..." admin="true" crud="false" /> is an
   * invalid configuration.
   */
  private $_hasCrud = true;

  /**
   * Create a new Model configuration object for the model with the given name.
   * By default, all models are part of the admin interface, as so also have a
   * CRUD service.
   *
   * @param string $modelName
   */
  public function __construct($modelName) {
    $this->_modelName = $modelName;
  }

  /**
   * Getter/setter  for whether or not the model is included in the admin
   * interface.  If a boolean value is given then the method acts as a setter,
   * otherwise it returns the current value.
   *
   * @param boolean Optional.
   * @return boolean If no parameter is given then the current value, otherwise
   *   the given parameter is echoed.
   * @deprecated Generated admin interface should no longer be used, use
   *   support provided by conductor app instead
   */
  public function hasAdmin($hasAdmin = null) {
    if ($hasAdmin !== null) {
      $this->_hasAdmin = (boolean) $hasAdmin;
    }
    return $this->_hasAdmin;
  }

  /**
   * Getter/setter  for whether or not the model has a generated CRUD service.
   * If a boolean value is given then the method acts as a setter, otherwise
   * it returns the current value.
   *
   *
   * @param boolean Optional.
   * @return boolean If no parameter is given then the current value, otherwise
   *   the given parameter is echoed.
   */
  public function hasCrud($hasCrud = null) {
    if ($hasCrud !== null) {
      $this->_hasCrud = (boolean) $hasCrud;
    }
    return $this->_hasCrud;
  }

  /**
   * Getter for the name of the model represented by the instance.
   *
   * @return string
   */
  public function getModelName() {
    return $this->_modelName;
  }
}
