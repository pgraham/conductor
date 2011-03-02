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
 * This file sets up the environment for running tests.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package conductor/test
 */
namespace conductor\test;

use \PHPUnit_Framework_TestSuite as TestSuite;

require_once __DIR__ . '/test-common.php';
require_once REED_PATH . '/test/AllTests.php';
require_once BASSOON_PATH . '/test/AllTests.php';
require_once CLARINET_PATH . '/test/AllTests.php';
require_once OBOE_PATH . '/test/AllTests.php';

/**
 * This class build a suite consisting of all tests for conductor.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/test
 */
class AllTests {

  public static function suite() {
    $suite = new TestSuite('All Clarinet Tests');

    $suite->addTestSuite('conductor\test\config\ModelTest');

    // Since conductor relies on reed, clarinet, bassoon and oboe, include
    // their test suites
    $suite->addTest(\reed\test\AllTests::suite());
    $suite->addTest(\bassoon\test\AllTests::suite());
    $suite->addTest(\clarinet\test\AllTests::suite());
    $suite->addTest(\oboe\test\AllTests::suite());

    return $suite;
  }
}
