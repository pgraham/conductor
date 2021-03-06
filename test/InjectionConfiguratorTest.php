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
use \Mockery as M;

use \zpt\cdt\compile\DependencyInjectionCompiler;
use \zpt\cdt\di\Injector;
use \zpt\pct\CodeTemplateParser;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the InjectionConfigurator instance generated by the 
 * DependencyInjectionCompiler.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class InjectionConfiguratorTest extends TestCase {

	protected function setUp() {
		$target = __DIR__ . '/target';
		if (file_exists($target)) {
			exec("rm -r $target");
		}
		mkdir($target);
	}

	public function testInjectionConfiguratorGeneration() {
		$pdoWrapperMock = M::mock('alias:zpt\orm\PdoWrapper');
		$pdoWrapperMock
			->shouldReceive('get')
			->andReturn((object) array());

		$diCompiler = new DependencyInjectionCompiler();	
		$diCompiler->setTemplateParser(new CodeTemplateParser());
		$diCompiler->addBean('myBean', 'stdclass');
		$diCompiler->compile(array( 'target' => __DIR__ . '/target'), 'mySite');

		// Make sure that an InjectionConfigurator can be instantiated
		$configuratorPath = __DIR__ . '/target/zpt/dyn/InjectionConfigurator.php';
		$this->assertFileExists($configuratorPath);

		require_once $configuratorPath;
		$configurator = new \zpt\dyn\InjectionConfigurator();

		// Make sure that the configurator adds the appropriate bean to the 
		// injection container
		$configurator->configure();

		$bean = Injector::getBean('myBean');
		$this->assertInstanceOf('stdclass', $bean);
	}
}
