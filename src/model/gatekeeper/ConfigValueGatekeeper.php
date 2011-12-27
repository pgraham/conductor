<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace conductor\model\gatekeeper;

use \conductor\crud\DefaultGatekeeper;

/**
 * Gatekeeper for the ConfigValue model CRUD service.  Same as DefaultGatekeeper
 * with added protection to not be able to update parameters not listed as
 * editable.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigValueGatekeeper extends DefaultGatekeeper {

  /**
   * There is no use in creating configuration values as part of site
   * functionality.  Configuration values should be created as part of a
   * database alter.
   */
  public function canCreate($model) {
    return false;
  }

  /**
   * Don't allow configuration values to be deleted.  If they are no longer
   * necessary they should be removed with an alter.
   */
  public function canDelete($model) {
    return false;
  }

  /**
   * Only allow editable configuration values to be updated.
   */
  public function canWrite($model) {
    if (parent::canWrite($model)) {
      return $model->getEditable();
    }
  }
}
