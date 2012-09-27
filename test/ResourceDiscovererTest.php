<?php
/*
 * Copyright Philip Graham, 2012
 * All Rights Reserved.
 */
namespace zpt\cdt\test;

require_once __DIR__ . '/test-common.php';

use \zpt\cdt\compile\ResourceDiscoverer;
use \Mockery;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * This class tests the resource discoverer class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceDiscovererTest extends TestCase {

  private $_resourceDiscoverer;
  private $_fileLister;

  protected function setUp() {
    $this->_resourceDiscoverer = new ResourceDiscoverer('/home/user/src', 'js');

    $this->_fileLister = Mockery::mock('zpt\util\file\FileLister');
    $this->_resourceDiscoverer->setFileLister($this->_fileLister);
  }

  protected function tearDown() {
    Mockery::close();
  }

  /**
   * Tests that a group with no setup, subgroups or init scripts works as
   * expected.
   */
  public function testSimpleGroup() {
    $testDir = '/home/user/src';
    $testExt = 'js';
    $testGroup = 'my.group';

    $fileMatches = array("$testGroup-s1.$testExt", "$testGroup-s1.$testExt");

    $this->_fileLister
      ->shouldReceive('matchesInDirectory')
      ->with($testDir, "$testGroup-*.$testExt")
      ->once()
      ->andReturn($fileMatches);

    $this->_fileLister
      ->shouldReceive('matchesInDirectory')
      ->with($testDir, "$testGroup.*-*.$testExt")
      ->once()
      ->andReturn(array());

    $this->_fileLister
      ->shouldReceive('directoryContains')
      ->with($testDir, "$testGroup.$testExt")
      ->once()
      ->andReturn(false);

    $actual = $this->_resourceDiscoverer->discover($testGroup);

    $expected = $fileMatches;
    $this->assertEquals($expected, $actual);
  }
}
