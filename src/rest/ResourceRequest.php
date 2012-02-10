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
 * This class encapsulates data about a resource request.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceRequest {

  /* Data sent with the request. */
  private $_data;

  /* Query parameters sent with the request. */
  private $_query;

  /**
   * Getter for the request data.
   *
   * @return array
   */
  public function getData() {
    return $this->_data;
  }

  /**
   * Getter for the request's query parameters.
   *
   * @return array
   */
  public function getQuery() {
    return $this->_query;
  }

  /**
   * Setter for any raw data sent with the request.  This is typically the
   * request body, preprocessed by PHP.
   *
   * @param array $data
   */
  public function setData(array $data = null) {
    $this->_data = $data;
  }

  /**
   * Setter for any query parameters sent with the request.
   *
   * @param array $query
   */
  public function setQuery(array $query = null) {
    $this->_query = $query;
  }

}
