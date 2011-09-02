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

use \conductor\Auth;

/**
 * Default Gatekeeper.  Allows read access to all users and create, write and
 * delete access to users with cdt-admin permissions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DefaultGatekeeper extends AbstractGatekeeper implements Gatekeeper {

  public function __construct($modelClass) {
    parent::__construct($modelClass);
  }

  public function canCreate($model) {
    return Auth::hasPermission('cdt-admin');
  }

  public function canDelete($model) {
    return Auth::hasPermission('cdt-admin');
  }

  public function canRead($model) {
    return true;
  }

  public function canWrite($model) {
    return Auth::hasPermission('cdt-admin');
  }
}