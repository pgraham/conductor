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
 * @package conductor
 */
namespace conductor;
use \Oboe\Item;

/**
 * This interface defines a page template.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
interface Template extends Item\Body {

  /**
   * This method is responsible for returning an ElementComposite to which
   * page content will be added.
   *
   * @return Oboe_ElementComposite must implement Oboe_Item_Body, can not be
   *     null
   */
  public function getContentContainer();

  /**
   * This method can optionally define a "base" for the page title.
   * E.g. if the title base is defined as "Reed, get it wet" then a call to
   * Oboe\Page / Reed_Page::dump("News") would result in the page's title being
   * "Reed, get it wet - News"
   *
   * @return string can be null
   */
  public function getBaseTitle();

  /**
   * If debug capturing is turned on with output enabled a template can define
   * the container for displaying.  If none is defined one will be appended
   * to the body as the last item to output.
   *
   * @return Oboe_ElementComposite must implement Oboe_Item_Body, can be null
   */
  public function getDebugContainer();
}
