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

use \conductor\crud\DefaultGatekeeper;
use \conductor\Conductor;

/**
 * Gatekeeper for the Photo model CRUD service.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PhotoGatekeeper extends DefaultGatekeeper {

  /**
   * Make sure that an image file actually exists for the photo before allowing
   * it to be read.
   *
   * @param Photo $model
   * @return boolean
   */
  public function canRead($model) {
    $pathInfo = Conductor::getPathInfo();
    $fileName = $model->getId() . '.' . $model->getImgtype();
    $imgPath = $pathInfo->getDocumentRoot() . '/usr/gallery';
    $filePath = "$imgPath/$fileName";
    return file_exists($filePath);
  }
}
