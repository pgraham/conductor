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
use \oboe\Element;
use \reed\File;

/**
 * Static interface for Bassoon service proxies.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServiceProxy {

  public static function getCrud($modelClass) {
    $srvc = new CrudService($modelClass);
    if (Conductor::isDebug()) {
      $srvc->generate();
    }

    return Element::js($srvc->getWebPath());
  }

  public static function get($srvcClass) {
    $srvc = new RemoteService($srvcClass);
    $srvcName = $srvc->getName();

    $pathInfo = Conductor::getPathInfo();
    $webTarget = $pathInfo->getWebTarget();
    $proxyPath = File::joinPaths($webTarget, 'js', "$srvcName.js");
    $proxyWeb = $pathInfo->fsToWeb($proxyPath);

    if (Conductor::isDebug()) {
      $dispatchPath = File::joinPaths($webTarget, 'ajx', "$srvcName");
      $dispatchWeb = $pathInfo->fsToWeb($dispatchPath);
      $srvc->generate($proxyPath, $dispatchPath, $dispatchWeb);
    }

    return Element::js($proxyWeb);
  }
}
