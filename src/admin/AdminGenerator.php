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
 * @package conductor/admin
 */
namespace conductor\admin;

/**
 * This class generates the javascript for editing a set of model classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/admin
 */
class AdminGenerator {

  private $_models;

  /**
   * Create a new generator for the given set of model classes.
   *
   * @param array $models List of model class names.
   */
  public function __construct(array $models) {
    $this->_models = $models;
  }

  /**
   * Generate the javascript and output it to the given directory.
   *
   * @param string $outputPath The path for where to write the generated
   *   javascript file.
   */
  public function generate($outputPath) {

  }
}
