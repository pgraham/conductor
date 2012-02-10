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
 * This class represents a media type specified in a request's "Accept: "
 * header.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AcceptType {

  /* The media type's associated q-value. */
  private $_q;

  /* The media type's sub-type. */
  private $_subtype;

  /* The media type's type. */
  private $_type;

  /**
   * Create a new media type for the given type/subtype with a q value of 1.
   *
   * @param string $type
   * @param string $subtype
   */
  public function __construct($type, $subtype) {
    $this->_type = $type;
    $this->_subtype = $subtype;
    $this->_q = 1;
  }

  /**
   * Return a string representation of the accepted media type along with
   * q-value information.
   *
   * @return string
   */
  public function __toString() {
    return "$this->_type/$this->_subtype ($this->_q)";
  }

  /**
   * Return a string representation of the accepted type's media type.  No
   * q-value information is included.  This value is acceptable for use as a
   * response's Content-Type header.
   *
   * @return string
   */
  public function asMediaType() {
    return "$this->_type/$this->_subtype";
  }

  /**
   * Getter for the media type's associated q-value.
   *
   * @return float
   */
  public function getQValue() {
    return $this->_q;
  }

  /**
   * Returns a boolean indicating whether or not the media type represented by
   * this object matches the given media type.
   *
   * @param string $mimeType
   * @return boolean
   */
  public function matches($mimeType) {
    if ($mimeType === '*/*' || $this->_type === '*') {
      return true;
    }

    list($type, $subtype) = explode('/', $mimeType, 2);
    if ($this->_type === $type) {
      if ($subtype === '*' ||
          $this->_subtype === '*' ||
          $this->_subtype === $subtype)
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Setter for the media type's associated q-value.
   *
   * @param float $q
   */
  public function setQValue($q) {
    $this->_q = $q;
  }

}
