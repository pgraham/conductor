<?php
namespace Conductor;

use \Bassoon\Generator;
use \Bassoon\RemoteService;

use \Oboe\Head\Javascript;
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
 * @package Conductor
 */
/**
 * This class is adds the client side proxy for a Bassoon service to the HEAD
 * element.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package Conductor
 */
class ServiceProxy {

  private $_elm;

  public function __construct($serviceClass) {
    $srvc = new RemoteService($serviceClass);
    $this->_elm = new Javascript($srvc->getProxyWebPath());

    $gen = new Generator($srvc);
    $gen->generate();
  }

  public function getElement() {
    return $this->_elm;
  }
}
