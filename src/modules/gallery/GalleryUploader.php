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

use \clarinet\Persister;
use \reed\File;
use \reed\Image;

/**
 * This class handles uploading a file for a gallery.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class GalleryUploader {

  const DEFAULT_MAX_DIMENSION = 1000;

  private static $_supportedTypes = array(
    IMAGETYPE_GIF =>  'gif',
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png'
  );

  private $_file;
  private $_maxDimension;

  public function __construct($maxDimension = null) {
    if ($maxDimension === null) {
      $maxDimension = self::DEFAULT_MAX_DIMENSION;
    }
    $this->_maxDimension = $maxDimension;

    if (isset($_GET['qqfile'])) {
      $this->_file = new UploadedFileXhr();
    } else if (isset($_FILES['qqfile'])) {
      $this->_file = new UploadedFileForm();
    } else {
      throw new Exception("No file uploaded");
    }
  }

  public function handleUpload($uploadDir) {
    if (!$this->_file->isImage()) {
      throw new Exception("Only images can be uploaded to the gallery");
    }

    $imgInfo = $this->_file->getImageInfo();
    $imgType = $imgInfo[2];
    if (!array_key_exists($imgType, self::$_supportedTypes)) {
      throw new Exception("Image is not a supported type");
    }
    $extension = self::$_supportedTypes[$imgType];

    $photo = new Photo();
    $photo->setImgtype($extension);
    $persister = Persister::get($photo);
    $persister->save($photo);
    $photoId = $photo->getId();

    $path = File::joinPaths($uploadDir, "$photoId.$extension");
    if (!$this->_file->save($path)) {
      throw new Exception("Could not save file to $uploadDir");
    }

    $img = new Image($path);
    if ($imgInfo[0] > $this->_maxDimension ||
        $imgInfo[1] > $this->_maxDimension)
    {
      if ($imgInfo[0] > $imgInfo[1]) {
        $img->resizeToWidth($this->_maxDimension);
      } else {
        $img->resizeToHeight($this->_maxDimension);
      }
      $img->save(); 
    }

    $thumb = new Image($path);
    $thumbPath = File::joinPaths($uploadDir, "thumb_$photoId.$extension");
    if ($imgInfo[0] > $imgInfo[1]) {
      $thumb->resizeToWidth(100);
    } else {
      $thumb->resizeToHeight(100);
    }
    $thumb->save($thumbPath);
    return array('success' => true);
  }
}
