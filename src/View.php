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
namespace conductor;

/**
 * Interface for classes that encapsulate a view.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface View {

  /**
   * This method is responsible for returning a string or stringable object
   * representing the HTML content of the page.
   *
   * @return string|stringable
   */
  public function getContent();

}
