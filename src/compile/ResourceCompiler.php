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

use \conductor\Resource;

use \reed\generator\CodeTemplate;
use \reed\WebSitePathInfo;

/**
 * This class performs compilcation for {@link Resource} instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompiler implements Compiler {

  private $_resource;

  public function __construct(Resource $resource) {
    $this->_resource = $resource;
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $target = $pathInfo->getWebTarget();

    $type = $this->_resource->getType();
    $name = $this->_resource->getName();
    $path = Resource::getResourcePath($name);

    if ($type !== null) {
      $target .= "/$type";
    }

    if (!file_exists($target)) {
      mkdir($target, 0755, true);
    }

    if ($values === null) {
      copy($path, "$target/" . basename($name));
    } else {
      CodeTemplate::compile($path, "$target/$name", $values);
    }
  }
}
