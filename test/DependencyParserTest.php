<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\cdt;

use \PHPUnit_Framework_TestCase as TestCase;

use \zpt\cdt\di\DependencyParser;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the dependency parser for annotation based dependency 
 * injection configuration.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyParserTest extends TestCase {

	/**
	 * Test that a class with a property marked for injection where the injected
	 * bean id is the same as the property name is parsed properly.
	 */
	public function testParsePropertyRefSameAsName() {
		$classDef = <<<CLASS
class DiPropertyRefSameAsName {
	/** @Injected */
	private \$bean;

	public function setBean(\$bean) {
		\$this->bean = \$bean;
	}
}
CLASS;
		eval($classDef);

		$deps = DependencyParser::parse('myBean', 'DiPropertyRefSameAsName');

		$this->assertArrayHasKey('props', $deps);
		$this->assertInternalType('array', $deps['props']);
		$this->assertCount(1, $deps['props']);
		$this->assertInternalType('array', $deps['props'][0]);

		$props = $deps['props'][0];
		$this->assertArrayHasKey('name', $props);
		$this->assertEquals('bean', $props['name']);
		$this->assertArrayHasKey('ref', $props);
		$this->assertEquals('bean', $props['ref']);
	}

	public function testParsePropertyRefSpecified() {
		$classDef = <<<CLASS
class DiPropertyRefSpecified {

	/**
	 * @Injected(ref = aBean)
	 */
	private \$bean;

	public function setBean(\$bean) { \$this->bean = \$bean; }
}
CLASS;
		eval($classDef);

		$deps = DependencyParser::parse('myBean', 'DiPropertyRefSpecified');

		$this->assertArrayHasKey('props', $deps);
		$this->assertInternalType('array', $deps['props']);
		$this->assertCount(1, $deps['props']);
		$this->assertInternalType('array', $deps['props'][0]);

		$props = $deps['props'][0];
		$this->assertArrayHasKey('name', $props);
		$this->assertEquals('bean', $props['name']);
		$this->assertArrayHasKey('ref', $props);
		$this->assertEquals('aBean', $props['ref']);
	}

	public function testParseConstructorArg() {
		$classDef = <<<'CLASS'
class DiConstructorArg {

	private $bean;

	/**
	 * @ctorArg ref = bean
	 */
	public function __construct($bean) {
		$this->bean = $bean;
	}
}
CLASS;
		eval($classDef);

		$deps = DependencyParser::parse('myBean', 'DiConstructorArg');

		$this->assertArrayHasKey('ctor', $deps);
		$this->assertInternalType('array', $deps['ctor']);
		$this->assertCount(1, $deps['ctor']);

		$ctor = $deps['ctor'][0];
		$this->assertEquals('$bean', $ctor);
	}
}
