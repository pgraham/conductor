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
namespace conductor;

use \bassoon\RemoteService;
use \clarinet\model\Parser as ModelParser;
use \conductor\generator\CrudServiceGenerator;
use \conductor\generator\CrudServiceInfo;
use \reed\File;

/**
 * This class encapsulates information about a CRUD remote service for a model
 * class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudService {

  private $_proxyPath;
  private $_srvcInfo;
  private $_webPath;

  public function __construct($modelClass) {
    $pathInfo = Conductor::getPathInfo();
    $model = ModelParser::getModel($modelClass);

    $this->_srvcInfo = new CrudServiceInfo($model);

    $srvcName = $this->_srvcInfo->getServiceName();
    $webTarget = $pathInfo->getWebTarget();
    $this->_proxyPath = File::joinPaths($webTarget, "js/$srvcName.js");
    $this->_webPath = $pathInfo->fsToWeb($this->_proxyPath);
  }

  public function generate() {
    $pathInfo = Conductor::getPathInfo();
    $cdtPath = File::joinPaths($pathInfo->getLibPath(), 'conductor');

    $generator = new CrudServiceGenerator($this->_srvcInfo);
    $generator->generate($pathInfo->getTarget(), $cdtPath);

    // Generate the Bassoon service proxy for the CRUD service class.
    $srvc = new RemoteService($this->_srvcInfo->getServiceClass());
    $srvcName = $this->_srvcInfo->getServiceName();

    $dispatchPath = File::joinPaths($pathInfo->getWebTarget(), "ajx/$srvcName");
    $dispatchWeb = $pathInfo->fsToWeb($dispatchPath);
    $srvc->generate($this->_proxyPath, $dispatchPath, $dispatchWeb);
  }

  public function getWebPath() {
    return $this->_webPath;
  }
}