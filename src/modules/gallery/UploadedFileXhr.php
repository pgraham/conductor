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
namespace conductor\modules\gallery;

/**
 * This class represents a file that has been uploaded using an XHR request.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class UploadedFileXhr {

  private $_tmp;
  private $_imgInfo;

  public function __construct() {
    $this->_tmp = tempnam(sys_get_temp_dir(), 'eandp');

    $input = fopen('php://input', 'r');
    $tmp = fopen($this->_tmp, 'w');
    stream_copy_to_stream($input, $tmp);

    $this->_imgInfo = getimagesize($this->_tmp);
  }

  public function __destruct() {
    unlink($this->_tmp);
  }

  public function getImageInfo() {
    return $this->_imgInfo;
  }

  public function isImage() {
    return $this->_imgInfo !== false;
  }

  public function save($path) {
    return copy($this->_tmp, $path);
  }
}
