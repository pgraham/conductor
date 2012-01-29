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
 * This class represents a file that was uploaded using a form submit.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class UploadedFileForm implements UploadedFile {

  private $_imgInfo;

  public function __construct() {
    $this->_imgInfo = getimagesize($_FILES['qqfile']['tmp_name']);
  }

  public function getImageInfo() {
    return $this->_imgInfo;
  }

  public function isImage() {
    return $this->_imgInfo !== false;
  }

  public function save($path) {
    return move_uploaded_file($_FILES['qqfile']['tmp_name'], $path);
  }
}
