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

  public function build(Property $model) {
    $templateValues = Array
    (
    );

    return $this->_templateLoader->load(
      'property-input-text.js', $templateValues);
  }
}
