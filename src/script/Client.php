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

use \conductor\Conductor;
use \oboe\head\Javascript;

/**
 * This class encasulates the client side component of conductor.
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
  public function __construct() {
    $docRoot = Conductor::$config['documentRoot'];
    $webRoot = Conductor::$config['webRoot'];
    $webWrite = Conductor::$config['webWritable'];

    $outputPath = $webWrite;
    $webOutputPath = str_replace($docRoot, '', $webWrite);

    if ($webRoot != '/') {
      $webOutputPath = $webRoot . $webOutputPath;
    }

    if (defined('DEBUG') && DEBUG === true) {
      $srcJsPath = __DIR__ . '/conductor.js';
      copy($srcJsPath, $webWrite . '/js/conductor.js');
    }

    parent::__construct($webOutputPath . '/js/conductor.js');
  }
}
