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

use \bassoon\Generator;
use \bassoon\RemoteService;

use \oboe\head\Javascript;

/**
 * This class is adds the client side proxy for a Bassoon service to the HEAD
 * element.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class ServiceProxy {

  private $_elm;

  public function __construct($serviceClass) {
    $docRoot = Conductor::$config['documentRoot'];
    $webRoot = Conductor::$config['webRoot'];
    $webWrite = Conductor::$config['webWritable'];

    $outputPath = $webWrite;

    if (strpos($docRoot, $webWrite) !== false) {
      $webOutputPath str_replace($docRoot, '', $webWrite);
    }
    if ($webRoot != '/') {
      $webOutputPath = $webRoot . $webOutputPath;
    }
    
    $srvc = new RemoteService($serviceClass);

    $gen = new Generator($srvc);
    $pathInfo = $gen->generate($outputPath, $webOutputPath);

    $this->_elm = new Javascript($pathInfo->getProxyWebPath());
  }

  public function getElement() {
    return $this->_elm;
  }
}
