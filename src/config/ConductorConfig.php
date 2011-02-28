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
 * @package conductor/config
 */
namespace conductor\config;

use \conductor\Conductor;
use \reed\Config;
/**
 * This is a custom reed configuration class for conductor.  It simply reads the
 * values parsed from conductor.cfg.xml
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/config
 */
class ConductorConfig extends Config {
  
  protected function documentRoot() {
    return Conductor::$config['documentRoot'];
  }

  protected function webSiteRoot() {
    return Conductor::$config['webRoot'];
  }

  protected function webWritableDir() {
    return Conductor::$config['webWritable'];
  }
}
