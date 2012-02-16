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
class CrudServiceBuilder {

  private $_srvcInfo;

  /**
   * Create a new service builder.
   *
   * @param ModelInfo $model
   */
  public function __construct(CrudServiceInfo $srvcInfo) {
    $this->_srvcInfo = $srvcInfo;
  }

  /**
   * Create the source code for a CRUD service class for the model encapsulated
   * by the instance.
   *
   * @param string $cdtPath Path to the conductor install that will be used by
   *   the CRUD service class.
   * @return string
   */
  public function build($cdtAutoloaderPath) {
    $templateValues = Array(
      'autoloader' => $cdtAutoloaderPath,
      'className'  => $this->_srvcInfo->getModel()->getActor(),
      'gatekeeper' => $this->_srvcInfo->getModel()->getGatekeeper(),
      'display'    => $this->_srvcInfo->getDisplayName(),
      'idColumn'   => $this->_srvcInfo->getModel()->getId()->getColumn(),
      'model'      => $this->_srvcInfo->getModel()->getClass(),
      'ns'         => CrudServiceInfo::CRUD_SERVICE_NS,
      'proxyName'  => $this->_srvcInfo->getProxyName()
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
    return $this->_srvcInfo->getModel()->getProperties();
  }
}
