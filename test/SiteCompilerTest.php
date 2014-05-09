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

use \zpt\cdt\compile\SiteCompiler;
use \ArrayObject;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests site compilation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SiteCompilerTest extends TestCase {

	private $compiler;
	private $mockConfig;

	protected function setUp() {
		$this->compiler = new SiteCompiler();

		$root = __DIR__ . '/mock-site';

		$this->mockConfig = array(
			'pathInfo' => new ArrayObject(array(
				'root' => $root,
				'webRoot' => '/',
				'htdocs' => "$root/htdocs",
				'cdtRoot' => "$root/vendor/zeptech/conductor",
				'src' => "$root/src",
				'target' => "$root/target"
			)),
			'namespace' => 'test',
			'db_config' => array(),
			'env' => 'dev',
			'logDir' => $root
		);
		$this->createMockConfigurator();
	}

	protected function tearDown() {
		M::close();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testSiteCompile() {
		$pathInfo = $this->mockConfig['pathInfo'];

		// Create a mock Compiler instance for compilation steps that have no
		// additional checking
		$mockCompiler = M::mock('zpt\cdt\compile\Compiler');
		$mockCompiler->shouldReceive('compile')->withAnyArgs()->zeroOrMoreTimes();

		// Mock the Configuration compiler
		$configCompiler = M::mock('zpt\cdt\compile\ConfigurationCompiler');
		$configCompiler
			->shouldReceive('compile')
			->once()
			->with($pathInfo['root'], 'dev')
			->andReturnNull();

		// Mock the resource compiler
		$resourceCompiler = M::mock('zpt\cdt\compile\resource\ResourceCompiler');
		$resourceCompiler
			->shouldReceive('compile')
			->with(
				$pathInfo['cdtRoot'] . '/resources/base.tmpl.js',
				$pathInfo['target'] . '/htdocs/js/base.js',
				array(
					'rootPath' => $pathInfo['webRoot'],
					'jsns' => $this->mockConfig['namespace']
				)
			)
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[cdtRoot]/htdocs/js",
				"$pathInfo[target]/htdocs/js"
			)
			->once()
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[cdtRoot]/htdocs/css",
				"$pathInfo[target]/htdocs/css"
			)
			->once()
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[cdtRoot]/htdocs/img",
				"$pathInfo[target]/htdocs/img"
			)
			->once()
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[src]/resources/js",
				"$pathInfo[target]/htdocs/js"
			)
			->once()
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[src]/resources/css",
				"$pathInfo[target]/htdocs/css"
			)
			->once()
			->andReturnNull();

		$resourceCompiler
			->shouldReceive('compile')
			->with(
				"$pathInfo[src]/resources/img",
				"$pathInfo[target]/htdocs/img"
			)
			->once()
			->andReturnNull();

		$diCompiler = M::mock('zpt\cdt\compile\DependencyInjectionCompiler');
		$diCompiler
			->shouldReceive('setTemplateParser')
			->with(anInstanceOf('zpt\pct\CodeTemplateParser'));

		$diCompiler
			->shouldReceive('addFile')
			->with("$pathInfo[cdtRoot]/resources/dependencies.xml")
			->once();
		$diCompiler
			->shouldReceive('addFile')
			->with("$pathInfo[src]/resources/dependencies.xml")
			->once();
		$diCompiler
			->shouldReceive('compile')
			->with(anInstanceOf('ArrayObject'), typeOf('string'))
			->once();

		//$resourceCompiler->shouldReceive('compile');

		$this->compiler->setConfigurationCompiler($configCompiler);
		$this->compiler->setDispatcherCompiler($mockCompiler);
		$this->compiler->setResourceCompiler($resourceCompiler);
		$this->compiler->setDependencyInjectionCompiler($diCompiler);
		$this->compiler->compile($pathInfo['root']);
	}

	private function createMockConfigurator() {
		M::mock('alias:zpt\dyn\Configurator')
			->shouldReceive('getConfig')
			->withNoArgs()
			->atLeast()->once()
			->andReturn($this->mockConfig);
	}
}
