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
namespace conductor\resources;

/**
 * This class encapsulates a font resource.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class FontResource {

  private $_family;
  private $_variants;

  public function __construct($family) {
    $this->_family = $family;
  }

  public function __toString() {
    if ($this->_variants !== null) {
      return $this->_family . ':' . implode(',', $this->_variants);
    }
    return $this->_family;
  }

  public function setVariants($variants) {
    if (is_string($variants)) {
      $variants = explode(' ', $variants);
    }

    $this->_variants = $variants;
  }
}
