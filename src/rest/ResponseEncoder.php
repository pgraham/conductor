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
namespace conductor\rest;

/**
 * Interface for objects which encode a response for a specific media type and
 * possibly a specific sub-type.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface ResponseEncoder {

  /**
   * Return a boolean indicating whether or not the encode can encode a response
   * for the given AcceptType.
   *
   * @param AcceptType $type
   * @return boolean
   */
  public function supports(AcceptType $type);

  /**
   * Return a value to echoed to the client for the given response.  The format
   * of the value needs to be in the format specified by the Accept header.
   *
   * @param ResourceResponse $response
   * @return string
   */
  public function encode(ResourceResponse $response);

}
