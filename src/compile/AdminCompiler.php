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

use \conductor\admin\AdminBuilder;
use \conductor\admin\AdminClient;
use \conductor\Resource;

use \reed\WebSitePathInfo;

/**
 * This class encapsulates the compilation of {@link AdminClient} instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminCompiler implements Compiler {

  private $_client;

  public function __construct(AdminClient $client) {
    $this->_client = $client;
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $resources = $this->_client->getResources();
    foreach ($resources AS $key => $resource) {
      if ($key === 'admin') {
        $builder = new AdminBuilder($values['models']);
        $adminValues = $builder->build();
        $resource->compile($pathInfo, $adminValues);

      } else if ($resource instanceof Resource) {
        $resource->compile($pathInfo);
      }
    }
  }
}
