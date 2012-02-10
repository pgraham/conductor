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
namespace conductor\compile;

use \reed\WebSitePathInfo;

/**
 * Marker interface for class that respresent a compilable resource.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Compilable {

  /**
   * This function is responsible for compressing a resource's source files in
   * order to improve performance.  Compilation should happen in-place and will
   * generally only be invoked durring a dedicated step of site deployment.
   *
   * @param WebSitePathInfo $pathInfo Path info for the site into which the
   *   library is being linked.
   */
  public function compile(WebSitePathInfo $pathInfo);

  /**
   * This function is responsible for including the resource in a document.
   *
   * @param WebSitePathInfo $pathInfo Path info for the site into which the
   *   library is being linked.
   * @param boolean $devMode Whether or not the site is operating in dev mode.
   */
  public function inc(WebSitePathInfo $pathInfo, $devMode);

  /**
   * This function is responsible for making a resource's files available within
   * a site's document root.
   *
   * @param WebSitePathInfo $pathInfo Path info for the site into which the
   *   library is being linked.
   * @param boolean $devMode Whether or not the site is operating in dev mode.
   */
  public function link(WebSitePathInfo $pathInfo, $devMode);

}
