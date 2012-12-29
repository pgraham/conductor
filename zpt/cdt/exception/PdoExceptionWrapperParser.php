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
namespace zpt\cdt\exception;

use \zeptech\orm\runtime\PdoExceptionWrapper;

/**
 * This class adds additional parsing to the information provided by the
 * PdoExceptionWrapper class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PdoExceptionWrapperParser
{
  
    const DUP_MSG = '/Duplicate entry \'(.+?)\' for key \'(.+?)\'/';
    const INVALID_FILTER = '/Unknown column \'(.+?)\' in \'where clause\'/';
    const INVALID_SORT = '/Unknown column \'(.+?)\' in \'order clause\'/';
    const NOT_NULL = '/Column \'(.+?)\' cannot be null/';

    private $responseInfo;

    // -------------------------------------------------------------------------
    // Only one of the following switches will be true
    // -------------------------------------------------------------------------
    private $isDuplicate = false;
    private $isInvalidFilter = false;
    private $isInvalidSort = false;
    private $isNotNullViolation = false;

    /**
     * Parse the information contained in the given PdoExceptionWrapper.
     *
     * @param PdoExceptionWrapper $e
     */
    public function __construct(PdoExceptionWrapper $e)
    {
        $mysqlCode = $e->getMysqlCode();
        $mysqlMsg = $e->getMysqlMsg();

        switch ($mysqlCode) {
            case '1048':
            if (preg_match(self::NOT_NULL, $mysqlMsg, $matches)) {
              $this->isNotNullViolation = true;
              $this->responseInfo = array(
                'field' => $matches[1]
              );
            }
            break;

            case '1054':
            if (preg_match(self::INVALID_FILTER, $mysqlMsg, $matches)) {
              $this->isInvalidFilter = true;
              $this->responseInfo = array(
                'filter' => $matches[1]
              );
            } else if (preg_match(self::INVALID_SORT, $mysqlMsg, $matches)) {
              $this->isInvalidSort = true;
              $this->responseInfo = array(
                'sort' => $matches[1]
              );
            }
            break;

            case '1062':
            if (preg_match(self::DUP_MSG, $mysqlMsg, $matches)) {
              $this->isDuplicate = true;
              $this->responseInfo = array(
                'field' => $matches[2],
                'value' => $matches[1]
              );
            }
            break;
        }
    }

    /**
     * Getter for any parsed information. What is contained will depend on the
     * type of error. The type can be determined using the is*() methods, only
     * one will return true for each parser instance.
     *
     * @return array
     */
    public function getResponseMessage() {
        return $this->_responseMessage;
    }

    public function isDuplicate() {
        return $this->isDuplicate;
    }

    public function isInvalidFilter() {
        return $this->isInvalidFilter;
    }

    public function isInvalidSort() {
        return $this->isInvalidSort;
    }
    
    public function isNotNullViolation() {
        return $this->isNotNullViolation;
    }
}
