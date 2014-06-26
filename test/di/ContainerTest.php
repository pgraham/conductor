<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\di\test;

use PHPUnit_Framework_TestCase as TestCase;
use zpt\cdt\di\Container;
use Mockery;

require_once __DIR__ . '/../test-common.php';

/**
 * This class test the dependency injection {@link Container} class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ContainerTest extends TestCase
{

	protected function tearDown() {
		Mockery::close();
	}

	public function testCreateEmptyContainer() {
		$container = new Container();

		$this->assertFalse($container->hasComponent('myComponent'));
		$this->assertFalse($container->hasComponentOfType('SomeClass'));

		$this->assertNull($container->getComponent('myComponent'));
		$this->assertNull($container->getComponentOfType('SomeClass'));

		$propsOfType = $container->getComponentsOfType('SomeClass');
		$this->assertInternalType('array', $propsOfType);
		$this->assertEmpty($propsOfType);
	}

	public function testPopulateContainer() {
		$container = new Container();

		$std1 = Mockery::mock('StdClass');
		$std2 = Mockery::mock('StdClass');
		$container->addComponent('myComponent', $std1);
		$container->addComponent($std2);

		$this->assertTrue($container->hasComponent('myComponent'));
		$this->assertTrue($container->hasComponentOfType('StdClass'));
		$this->assertTrue($container->hasComponentOfType('stdClass'));

		$byName = $container->getComponent('myComponent');
		$byType = $container->getComponentOfType('StdClass');
		$byTypeCol = $container->getComponentsOfType('StdClass');

		$this->assertSame($std1, $byName);
		$this->assertSame($std1, $byType);
		$this->assertSame($std1, $byTypeCol[0]);
		$this->assertSame($std2, $byTypeCol[1]);
	}
}
