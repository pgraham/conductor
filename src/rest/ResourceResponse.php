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
 * This class encapsulates data about a response to a resource request.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceResponse {

  /* Data the comprises the request body. */
  private $_data;

  /* Headers to send with the response. */
  private $_headers = array();

  /**
   * Getter for the response's raw unencoded data.
   *
   * @return mixed
   */
  public function getData() {
    return $this->_data;
  }

  /**
   * Getter for the response's headers.
   *
   * @return string[]
   */
  public function getHeaders() {
    return $this->_headers;
  }

  /**
   * Add a header to send with the response.
   *
   * @param string $header
   */
  public function header($header) {
    $this->_headers[] = $header;
  }

  /**
   * Setter for the response's raw unencoded data.
   *
   * @param mixed $data
   */
  public function setData($data = null) {
    $this->_data = $data;
  }

}
