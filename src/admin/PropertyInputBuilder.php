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

use \clarinet\model\Property;

use \conductor\Exception;

use \reed\generator\CodeTemplateLoader;

/**
 * Builds parameter arrays for populating the inputs section of the
 * model-form.js template
 * Populator for the property-input-*.js templates.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PropertyInputBuilder {

  public function build(Property $property) {
    $propName = $property->getIdentifier();

    $adminModelInfo = new AdminModelInfo($property->getModel());
    $propInfo = $adminModelInfo->getProperty($property->getIdentifier());

    return array(
      'type' => $property->getType(),
      'name' => "{$modelName}_{$propName}_input",
      'property' => $propName,
      'label'    => $propInfo->getDisplayName()
    );
  }
}
