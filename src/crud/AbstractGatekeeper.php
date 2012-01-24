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
namespace conductor\crud;

use \clarinet\ActorFactory;

use \conductor\auth\AuthorizationException;

/**
 * Base implementation for Gatekeepers.  Implements the four checkCan* methods,
 * Leave the four can*() methods for the implementations.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class AbstractGatekeeper implements Gatekeeper {

  private $_modelClass;
  private $_transformer;

  protected function __construct($modelClass) {
    $this->_modelClass = $modelClass;
    $this->_transformer = ActorFactory::getActor('transformer', $modelClass);
  }

  public function checkCanCreate($model) {
    if (!$this->canCreate($model)) {
      throw new AuthorizationException($this->msg($model, 'create'));
    }
  }

  public function checkCanDelete($model) {
    if (!$this->canDelete($model)) {
      throw new AuthorizationException($this->msg($model, 'delete'));
    }
  }

  public function checkCanRead($model) {
    if (!$this->canRead($model)) {
      throw new AuthorizationException($this->msg($model, 'read'));
    }
  }

  public function checkCanWrite($model) {
    if (!$this->canWrite($model)) {
      throw new AuthorizationException($this->msg($model, 'write'));
    }
  }

  protected function msg($model, $action) {
    $id = $this->_transformer->getId($model);
    $msg = "Unable to $action {$this->_modelClass}";
    if ($id) {
      $msg .= " with id $id";
    }

    $msg .= ": Permission Denied";
    return $msg;
  }
}
