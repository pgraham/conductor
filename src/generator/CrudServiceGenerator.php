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

use \clarinet\model\Model;
use \reed\File;
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

  private $_crudInfo;

  /**
   * Create a new generator for the given model info
   *
   * @param DecoartedModel $model The model for which to generate a service.
   */
  public function __construct($modelInfo) {
    if ($modelInfo instanceof Model) {
      $this->_model = $modelInfo;
      $this->_crudInfo = new CrudServiceInfo($modelInfo);
    } else if ($modelInfo instanceof CrudServiceInfo) {
      $this->_model = $modelInfo->getModel();
      $this->_crudInfo = $modelInfo;
    }
  }

  /**
   * Generate the services and output them to the given directory.
   *
   * @param string $outPath The path for where to write the generated files.
   *   generated files will be placed in a subdirectory of given path which
   *   corresponds to the generated service class's namespace.
   * @param string $cdtPath The path to the conductor install which will
   *   be used 
   */
  public function generate($outPath, $cdtPath) {
    $cdtAutoloaderPath = File::joinPaths($cdtPath, 'src/Autoloader.php');
    $builder = new CrudServiceBuilder($this->_model);
    $template = $builder->build($cdtAutoloaderPath);

    // Ensure the output directory exists
    $serviceRelPath = str_replace('\\', '/', CrudServiceInfo::CRUD_SERVICE_NS);
    $outDir = File::joinPaths($outPath, $serviceRelPath);
    if (!file_exists($outDir)) {
      mkdir($outDir, 0755, true);
    }

    $serviceFileName = $this->_crudInfo->getServiceName() . '.php';
    $servicePath = "$outDir/$serviceFileName";
    $file = new SplFileObject($servicePath, 'w');
    $file->fwrite($template);

    return $servicePath;
  }
}
