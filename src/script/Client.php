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
 * @package conductor/script
 */
namespace conductor\script;

use \oboe\head\Javascript;

use \reed\WebSitePathInfo;

/**
 * This class encapsulates the client side component of conductor.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/script
 */
class Client extends Javascript {

  /**
   * Create a new Javascript element for the conductor client script.
   *
   * If debug mode is enabled the script will be copied to the web
   * writable directory.
   */
  public function __construct(WebSitePathInfo $pathInfo) {
    $webTarget = $pathInfo->getWebTarget() . '/js';
    $webPath = $pathInfo->getWebAccessibleTarget() . '/js';

    if (defined('DEBUG') && DEBUG === true) {
      if (!file_exists($webTarget)) {
        mkdir($webTarget, 0755, true);
      }

      $srcJsPath = __DIR__ . '/conductor.js';
      copy($srcJsPath, $webTarget . '/conductor.js');
    }

    parent::__construct($webPath . '/conductor.js');
  }
}
