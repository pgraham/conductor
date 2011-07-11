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
namespace conductor\compile;

use \conductor\script\Client as ConductorClient;
use \conductor\Resource;

use \reed\WebSitePathInfo;

/**
 * This class performs compiler for {@link Client} instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 */
class ClientCompiler implements Compiler {

  private $_client;

  public function __construct(ConductorClient $client) {
    $this->_client = $client;
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $resources = $this->_client->getResources();

    $resources['working']->compile($pathInfo);
    $resources['utility']->compile($pathInfo);

    // Prepare template values for conductor.js resource
    $workingPath = Resource::getResourcePath('working.gif');
    $workingImgInfo = getimagesize($workingPath);

    $values = array(
      'rootPath'    => $pathInfo->getWebRoot(),
      'targetPath'  => $pathInfo->getWebAccessibleTarget(),
      'imgWidth'    => $workingImgInfo[0],
      'imgHeight'   => $workingImgInfo[1]
    );
    $resources['client']->compile($pathInfo, $values);
  }
}
