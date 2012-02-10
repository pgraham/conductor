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
namespace conductor\jslib;

use \conductor\compile\Compilable;
use \reed\WebSitePathInfo;

/**
 * Interface for adapters to commonly available libraries which do not live
 * whithin a site's document root.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Library extends Compilable {

  /**
   * A library instance is specific to its options, so these are required when
   * constructed
   *
   * @param WebSitePathInfo $pathInfo
   */
  public function __construct(array $options = null);

}
