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
namespace conductor\html;

/**
 * This interface defines a page template.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface PageTemplate { 

  /**
   * This method is responsible for returning an ElementComposite to which
   * page content will be added.
   *
   * @return Oboe_ElementComposite must implement Oboe_Item_Body, can not be
   *     null
   */
  public function initialize(Page $page);

}
