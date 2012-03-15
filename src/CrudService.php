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
use \conductor\generator\CrudServiceGenerator;
use \conductor\generator\CrudServiceInfo;
use \reed\File;
use \zeptech\orm\generator\model\Parser as ModelParser;

/**
 * This class encapsulates information about a CRUD remote service for a model
 * class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudService {

  private $_srvcInfo;
  private $_webPath;

  public function __construct($modelClass) {
    $pathInfo = Conductor::getPathInfo();
    $model = ModelParser::getModel($modelClass);

    $this->_srvcInfo = new CrudServiceInfo($model);
    $proxyName = $this->_srvcInfo->getProxyName();

    $webTarget = "$pathInfo[target]/htdocs";
    $proxyPath = File::joinPaths($webTarget, "js/$proxyName.js");
  }

  public function generate() {
    $pathInfo = Conductor::getPathInfo();
    $asWebPath = $pathInfo->asWebPath;

    $generator = new CrudServiceGenerator($this->_srvcInfo);
    $generator->generate($pathInfo);

    // Generate the Bassoon service proxy for the CRUD service class.
    $actor = $this->_srvcInfo->getModel()->getActor();

    $className = $this->_srvcInfo->getClassName();
    $srvc = new RemoteService($className, $pathInfo);
    $srvc->generate(
      "$pathInfo[target]/htdocs/js",
      "$pathInfo[target]/htdocs/ajx",
      $asWebPath('/ajx'));
  }
}
