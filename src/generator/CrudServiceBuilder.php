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

use \clarinet\model\Model;

use \reed\generator\CodeTemplateLoader;
use \reed\WebSitePathInfo;

/**
 * This class creates a bassoon service for a given model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceBuilder {

  private $_model;
  private $_pathInfo;

  /**
   * Create a new service builder.
   *
   * @param ModelInfo $model
   */
  public function __construct(Model $model, WebSitePathInfo $pathInfo)
  {
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

    $crudInfo = new CrudServiceInfo($this->_model);
    $templateValues = Array(
      'autoloader' => $autoloaderPath,
      'class'      => $crudInfo->getCrudServiceClass(),
      'className'  => $crudInfo->getCrudServiceName(),
      'model'      => $this->_model->getClass(),
      'ns'         => CrudServiceInfo::CRUD_SERVICE_NS,
      'idColumn'   => $this->_model->getId()->getColumn()
    );

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    return $templateLoader->load('crudService-template.php', $templateValues);
  }

  /**
   * Getter for the model's properties.
   *
   * @return clarinet\model\Property[]
   */
  public function getProperties() {
    return $this->_model->getProperties();
  }
}
