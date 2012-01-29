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
 * Model class for gallery photos.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = photos)
 * @ProxyName PhotoCrud
 * @Gatekeeper conductor\modules\gallery\PhotoGatekeeper
 */
class Photo {

  private $_id;
  private $_imgtype;
  private $_caption;
  private $_category;

  /**
   * @Id
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column
   */
  public function getImgtype() {
    return $this->_imgtype;
  }

  /**
   * @Column
   */
  public function getCaption() {
    return $this->_caption;
  }

  /**
   * @ManyToOne(entity = conductor\modules\gallery\Category, column = categories_id)
   */
  public function getCategory() {
    return $this->_category;
  }

  /*
   * ===========================================================================
   * Setters.
   * ===========================================================================
   */

  public function setId($id) {
    $this->_id = $id;
  }

  public function setImgtype($imgtype) {
    $this->_imgtype = $imgtype;
  }

  public function setCaption($caption) {
    $this->_caption = $caption;
  }

  public function setCategory(Category $category = null) {
    $this->_category = $category;
  }
}
