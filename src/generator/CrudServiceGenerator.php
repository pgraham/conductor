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
namespace conductor\generator;

use \SplFileObject;

use \conductor\model\DecoratedModel;

use \reed\WebSitePathInfo;

/**
 * This class generates a service class for a specified model. This class can
 * then be used by bassoon to generate the client-server communication code for
 * manipulating persisted model instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceGenerator {

  private $_model;

  /**
   * Create a new generator for the given model info
   *
   * @param array $models List of model class names.
   */
  public function __construct(DecoratedModel $model) {
    $this->_model = $model;
  }

  /**
   * Generate the services and output them to the given directory.
   *
   * @param string $outputPath The path for where to write the generated files.
   */
  public function generate(WebSitePathInfo $pathInfo) {
    $builder = new CrudServiceBuilder($this->_model, $pathInfo);
    $template = $builder->build();

    // Ensure the output directory exists
    $serviceRelPath = str_replace('\\', '/',
      CrudServiceModelDecorator::CRUD_SERVICE_NS);
    $outputPath = $pathInfo->getTarget() . '/' . $serviceRelPath;
    if (!file_exists($outputPath)) {
      mkdir($outputPath, 0755, true);
    }

    $serviceFileName = $this->_model->getCrudServiceName() . '.php';
    $servicePath = $outputPath . '/' . $serviceFileName;
    $file = new SplFileObject($servicePath, 'w');
    $file->fwrite($template);
  }
}