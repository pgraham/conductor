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
namespace conductor\crud;

use \zeptech\orm\runtime\PdoExceptionWrapper;
use \zeptech\orm\runtime\ValidationException;
use \Exception;

/**
 * This class provides information about an error that occurred while processing
 * a CRUD operation.  Exceptions of this type encapsulate errors at the level
 * of the model and it's relationships, e.g. validation, duplicates, etc.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudException extends Exception {

  const DUP_MSG = '/Duplicate entry \'(.+?)\' for key \'(.+?)\'/';
  const INVALID_FILTER = '/Unknown column \'(.+?)\' in \'where clause\'/';
  const INVALID_SORT = '/Unknown column \'(.+?)\' in \'order clause\'/';
  const NOT_NULL = '/Column \'(.+?)\' cannot be null/';

  private $_responseHeader = 'HTTP/1.1 400 Bad Request';
  private $_responseMessage;

  private $_isDuplicate = false;
  private $_isInvalidFilter = false;
  private $_isInvalidSort = false;
  private $_isNotNullViolation = false;

  public function __construct() {
    parent::__construct();

    $arg1 = func_get_arg(0);
    if ($arg1 instanceof PdoExceptionWrapper) {
      $this->_initiateFromPdoException($arg1);
    } else if ($arg1 instanceof ValidationException) {
      $this->_initiateFromValidationException($arg1);
    } else {
      $this->_responseHeader = $arg1;
      $this->_responseMessage = func_get_arg(1);
    }
  }

  public function getResponseHeader() {
    return $this->_responseHeader;
  }

  /**
   * Getter for the response to send along with the error response.  This
   * function will return either a single string as the message or an array.  In
   * the case of an array, it can either be a numerically indexed array of
   * messages or an set of key-value pairs that can be used to build a
   * specialized message.  In this case, to determine the nature of the error
   * use the isXXX() methods.
   *
   * @return string | array
   */
  public function getResponseMessage() {
    return $this->_responseMessage;
  }

  public function isDuplicate() {
    return $this->_isDuplicate;
  }

  public function isInvalidFilter() {
    return $this->_isInvalidFilter;
  }

  public function isInvalidSort() {
    return $this->_isInvalidSort;
  }
  
  public function isNotNullViolation() {
    return $this->_isNotNullViolation;
  }
  
  private function _initiateFromPdoException(PdoExceptionWrapper $e) {
    $mysqlCode = $e->getMysqlCode();
    $mysqlMsg = $e->getMysqlMsg();

    switch ($mysqlCode) {
      case '1048':
      if (preg_match(self::NOT_NULL, $mysqlMsg, $matches)) {
        $this->_isNotNullViolation = true;
        $this->_responseHeader = 'HTTP/1.1 403 Forbidden';
        $this->_responseMessage = array(
          'field' => $matches[1]
        );
      }
      break;

      case '1054':
      if (preg_match(self::INVALID_FILTER, $mysqlMsg, $matches)) {
        $this->_isInvalidFilter = true;
        $this->_responseHeader = 'HTTP/1.1 403 Forbidden';
        $this->_responseMessage = array(
          'filter' => $matches[1]
        );
      } else if (preg_match(self::INVALID_SORT, $mysqlMsg, $matches)) {
        $this->_isInvalidSort = true;
        $this->_responseHeader = 'HTTP/1.1 403 Forbidden';
        $this->_responseMessage = array(
          'sort' => $matches[1]
        );
      }
      break;

      case '1062':
      if (preg_match(self::DUP_MSG, $mysqlMsg, $matches)) {
        $this->_isDuplicate = true;
        $this->_responseHeader = 'HTTP/1.1 403 Forbidden';
        $this->_responseMessage = array(
          'field' => $matches[2],
          'value' => $matches[1]
        );
      }
      break;
    }
  }

  private function _initiateFromValidationException(ValidationException $e) {
    $hdr = 'HTTP/1.1 403 Forbidden';

    $msg = $e->getMessages();
    if (count($msg) === 1) {
      $msg = $msg[0];
    }

    $this->_responseHeader = $hdr;
    $this->_responseMessage = $msg;
  }
}
