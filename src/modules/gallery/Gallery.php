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

use \clarinet\Criteria;
use \clarinet\Persister;
use \conductor\Conductor;
use \oboe\Composite;
use \oboe\Element;

/**
 * Gallery Composite Element.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Gallery extends Composite {

  public function __construct($category = null) {
    $categoryPersister = Persister::get('conductor\modules\gallery\Category');
    $photoPersister = Persister::get('conductor\modules\gallery\Photo');

    // Determine the currently selected category
    $categories = $categoryPersister->retrieve();
    $activeCategory = null;
    if (isset($_GET['cat'])) {
      $cat = $categoryPersister->getById($_GET['cat']);
      if ($cat !== null) {
        $activeCategory = $cat;
      }
    }

    if ($activeCategory === null && isset($categories[0])) {
      $activeCategory = $categories[0];
    }

    // Build the category navigation
    $categoryNav = Element::ul();

    foreach ($categories AS $category) {
      // Don't show any categories that have no images
      $c = new Criteria();
      $c->addEquals('categories_id', $category->getId());
      $count = $photoPersister->count($c);
      if ($count === 0) {
        continue;
      }

      $categoryId = $category->getId();
      $link = Element::a("?gallery&cat=$categoryId", $category->getName());
      if ($categoryId === $activeCategory->getId()) {
        $link->addClass('sel');
      }

      $categoryNav->add($link);
    }

    $gallery = Element::div()->setId('gallery');
    $c = new Criteria();
    if ($activeCategory !== null) {
      $c->addEquals('categories_id', $activeCategory->getId());
    }
    $photos = $photoPersister->retrieve($c);

    $basePath = Conductor::getPathInfo()->webPath('/usr/gallery');
    foreach ($photos AS $photo) {
      $fileName = $photo->getId() . '.' . $photo->getImgtype();
      $thumbName = "thumb_" . $fileName;

      $thumb = Element::img("$basePath/$thumbName");
      $img = Element::a("$basePath/$fileName", $thumb);

      if ($photo->getCaption()) {
        $img->setAttribute('title', $photo->getCaption());
      }
      $gallery->add($img);
    }

    $this->initElement(
      Element::div()
        ->add(Element::div()->setId('galleryNav')->add($categoryNav))
        ->add($gallery)
    );
  }
}
