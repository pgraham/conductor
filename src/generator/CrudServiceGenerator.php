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

use \conductor\Conductor;
use \reed\File;
use \SplFileObject;

/**
 * This class generates a service class for a specified model. This class can
 * then be used by bassoon to generate the client-server communication code for
 * manipulating persisted model instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceGenerator {

  private $_crudInfo;

  /**
   * Create a new generator for the given model info
   *
   * @param DecoartedModel $model The model for which to generate a service.
   */
  public function __construct(CrudServiceInfo $modelInfo) {
    $this->_crudInfo = $modelInfo;
  }

  /**
   * Generate the services and output them to the given directory.
   *
   * @param string $outPath The path for where to write the generated files.
   *   generated files will be placed in a subdirectory of given path which
   *   corresponds to the generated service class's namespace.
   */
  public function generate($pathInfo) {
    $builder = new CrudServiceBuilder($this->_crudInfo);
    $template = $builder->build(Conductor::getConfig());

    // Ensure the output directory exists
    $serviceRelPath = str_replace('\\', '/', CrudServiceInfo::CRUD_SERVICE_NS);
    $outDir = "$pathInfo[target]/$serviceRelPath";
    if (!file_exists($outDir)) {
      mkdir($outDir, 0755, true);
    }

    $serviceFileName = $this->_crudInfo->getModel()->getActor() . '.php';
    $servicePath = "$outDir/$serviceFileName";
    $file = new SplFileObject($servicePath, 'w');
    $file->fwrite($template);

    return $servicePath;
  }

}
