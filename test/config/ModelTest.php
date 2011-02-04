<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package conductor/test/config
 */
namespace conductor\test\config;

use \conductor\config\Model;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests the model parsing functionality of the configuration parser.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/test/config
 */
class ModelTest extends TestCase {

  /**
   * Tests that the "scandir" attribute works as expected.
   */
  public function testScanDir() {
    $cfgFile = __DIR__ . '/scandir.cfg.xml';

    $xmlCfg = simplexml_load_file($cfgFile);

    $models = Model::parse($xmlCfg->models, __DIR__);
    $this->assertInternalType('array', $models);

    $this->assertEquals(3, count($models));
    $this->assertContains('Model1', $models);
    $this->assertContains('Model2', $models);
    $this->assertCOntains('subns\Model1', $models);

  }

  /**
   * Tests that the "scandir" attribute works as expected when a basens
   * attribute is present.
   */
  public function testScanDirBaseNs() {
    $cfgFile = __DIR__ . '/scandirns.cfg.xml';

    $xmlCfg = simplexml_load_file($cfgFile);

    $models = Model::parse($xmlCfg->models, __DIR__);
    $this->assertInternalType('array', $models);

    $this->assertEquals(3, count($models));
    $this->assertContains('nsbase\Model1', $models);
    $this->assertContains('nsbase\Model2', $models);
    $this->assertCOntains('nsbase\subns\Model1', $models);
  }
}
