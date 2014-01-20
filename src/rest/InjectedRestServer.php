<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\cdt\rest;

use \zpt\rest\RestServer;

/**
 * RestServer that can be configured with a list of BeanRequestHandlers which
 * are a RequestHandler implementation that declares it own mappings.  Mappings
 * are configured through DI.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class InjectedRestServer extends RestServer {

  /**
   * Add a BeanRequestHandler.  Bean request handlers provide their own
   * mapping information.
   *
   * @param BeanRequestHandler $handler
   */
  public function addBeanRequestHandler(BeanRequestHandler $handler) {
    $mappings = $handler->getMappings();

    foreach ($mappings as $mapping) {
      // TODO Parameter order to this method should be id, uri, method, handler
      $this->addMapping(
        $mapping->uri,
        $handler,
        isset($mapping->id) ? $mapping->id : null,
        isset($mapping->method) ? $mapping->method : null
      );
    }
  }

}
