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

use \reed\generator\CodeTemplateLoader;
use \reed\WebSitePathInfo;

/**
 * This class creates a bassoon service for a given model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class BassoonServiceBuilder {

  private $_model;
  private $_pathInfo;

  /**
   * Create a new service builder.
   *
   * @param ModelInfo $model
   */
  public function __construct(ModelInfo $model, WebSitePathInfo $pathInfo) {
    $this->_model = $model;
    $this->_pathInfo = $pathInfo;
  }

  /**
   * Create the source code for a CRUD service class for the model encapsulated
   * by the instance.
   *
   * @return string
   */
  public function build() {
    $autoloaderPath = $this->_pathInfo->getLibPath()
      . '/conductor/src/Autoloader.php';

    $templateValues = Array(
      'autoloader' => $autoloaderPath,
      'class'      => $this->_model->getCrudServiceClass(),
      'className'  => $this->_model->getCrudServiceName(),
      'model'      => $this->_model->getClassName(),
      'ns'         => ModelInfo::CRUD_SERVICE_NS
    );

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $crudService = $templateLoader->load('crudService.php', $templateValues);
    return $crudService;
  }
}
