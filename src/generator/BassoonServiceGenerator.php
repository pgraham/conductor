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
 * @package conductor/generator
 */
namespace conductor\generator;

/**
 * This class generates service classes for a set of model classes that can then
 * be used by bassoon to generate the client-server communication code for
 * manipulating persisted model instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/generator
 */
class BassoonServiceGenerator {

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
   * Generate the services and output them to the given directory.
   *
   * @param string $outputPath The path for where to write the generated files.
   */
  public function generate($outputPath) {
    // TODO
  }
}
