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
 * This class encapsulates the collection of available encoders.
 * This class implements the Factory Method pattern.  The concrete factory is
 * a private singleton instance of this class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class EncoderFactory {

  /* The factory instance. */
  private static $_instance;

  /**
   * Retrieve a response encoder for the given mime type.
   *
   * @param string $mimeType
   * @return ResponseEncoder
   */
  public static function getEncoder(AcceptType $type) {
    $factory = self::_getInstance();

    $encoders = $factory->getEncoders();
    foreach ($encoders AS $encoder) {
      if ($encoder->supports($type)) {
        return $encoder;
      }
    }
    return null;
  }

  /* Private instance method for the factory singleton. */
  private static function _getInstance() {
    if (self::$_instance === null) {
      self::$_instance = new EncoderFactory();
    }
    return self::$_instance;
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_encoders = array();

  protected function __construct() {
    $this->_encoders = array(
      new HtmlEncoder(),
      new TextEncoder(),
      new JsonEncoder()
    );
  }

  protected function getEncoders() {
    return $this->_encoders;
  }

}
