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
 * Populator for the property-input-*.js templates.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PropertyInputBuilder {
  
  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  public function build(Property $property) {
    $type = $property->getType();

    switch ($type) {
      case Property::TYPE_BOOLEAN:
      $template = 'property-input-boolean.js';
      break;

      case Property::TYPE_DATE:
      $template = 'property-input-date.js';
      break;

      case Property::TYPE_DECIMAL:
      $template = 'property-input-decimal.js';
      break;

      case Property::TYPE_FLOAT:
      $template = 'property-input-float.js';
      break;

      case Property::TYPE_INTEGER:
      $template = 'property-input-integer.js';
      break;

      case Property::TYPE_STRING:
      $template = 'property-input-string.js';
      break;

      case Property::TYPE_TEXT:
      $template = 'property-input-text.js';
      break;

      case Property::TYPE_TIMESTAMP:
      $template = 'property-input-timestamp.js';
      break;

      default:
      assert("false /*Unrecognized property type: $type */");
      return '';
    }

    return $this->_templateLoader->load($template,
      $this->_getTemplateValues($property));
  }

  private function _getTemplateValues($property) {
    $adminModelInfo = new AdminModelInfo($property->getModel());
    $propInfo = $adminModelInfo->getProperty($property->getIdentifier());
    return array(
      'model'    => $property->getModel()->getIdentifier(),
      'property' => $property->getIdentifier(),
      'label'    => $propInfo->getDisplayName(),
      'default'  => $property->getDefault()
    );
  }
}
