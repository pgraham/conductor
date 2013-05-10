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

use \zpt\rest\RequestHandler;

/**
 * Interface for RequestHandlers that provide their own mapping information to
 * a InjectedRestServer.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface BeanRequestHandler extends RequestHandler {

  /**
   * Return a list of StdClass objects that contain request mapping information:
   *
   *       id: An ID for the mapping
   *      uri: The URI template for the mapping
   *   method: The HTTP verb for the mapping
   */
  public function getMappings();
}
