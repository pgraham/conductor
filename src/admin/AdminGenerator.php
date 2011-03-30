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

use \SplFileObject;

use \conductor\model\ModelSet;

use \reed\WebSitePathInfo;

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
   * @param ModelSet $models Set of models for which to generate an interface.
   */
  public function __construct(ModelSet $models) {
    $this->_models = $models;

    $models->decorate(new AdminModelDecoratorFactory());
  }

  /**
   * Generate the javascript and output it to the given directory.  The
   * generated script will output to $outputPath . '/js/conductor-admin.js';
   *
   * @param string $outputPath The path for where to write the generated
   *   javascript file.
   */
  public function generate(WebSitePathInfo $pathInfo) {
    $builder = new AdminBuilder($this->_models);
    $template = $builder->build();

    // Ensure the output directory exists
    $outputPath = $pathInfo->getWebTarget() . '/js';
    if (!file_exists($outputPath)) {
      mkdir($outputPath, 0755, true);
    }

    $adminPath = $outputPath . '/conductor-admin.js';
    $file = new SplFileObject($adminPath, 'w');
    $file->fwrite($template);

    // Copy the stylesheet to the approprivate directory
    $outputPath = $pathInfo->getWebTarget() . '/css';
    if (!file_exists($outputPath)) {
      mkdir($outputPath, 0755, true);
    }
    copy(
      __DIR__ . '/conductor-admin.css',
      $outputPath . '/conductor-admin.css');
  }
}
