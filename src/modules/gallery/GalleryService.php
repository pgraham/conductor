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

use \conductor\auth\AuthorizationException;
use \conductor\Auth;

/**
 * Remote service for managing the site's photo gallery.
 *
 * @Service( name = GalleryService )
 * @CsrfToken conductorsessid
 * @Requires __ROOT__/src/common.php
 */
class GalleryService {

  public function movePhotosToCategory($categoryId, array $photoIds) {
    if (!Auth::hasPermission('cdt-admin')) {
      throw new AuthorizationException("Unable to move photos to category: "
        . "Permission Denied");
    }

    $categoryPersister = Persister::get('conductor\modules\gallery\Category');
    $photoPersister = Persister::get('conductor\modules\gallery\Photo');

    $category = null;
    if ($categoryId !== null) {
      $category = $categoryPersister->getById($categoryId);
      if ($category === null) {
        throw new \Exception("No category with id $categoryId exists");
      }
    }

    foreach ($photoIds AS $photoId) {
      $photo = $photoPersister->getById($photoId);
      if ($photo !== null) {
        $photo->setCategory($category);
        $photoPersister->save($photo);
      }
    }
  }
}
