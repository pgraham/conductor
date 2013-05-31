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

use \zpt\cdt\compile\resource\ResourceCompiler;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the ResourceCompiler.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompilerTest extends TestCase {

	protected function setUp() {

	}

	public function testTemplateResource() {
		$mockTemplateParser = M::mock('zpt\pct\CodeTemplateParser');
		$mockTemplate = M::mock('zpt\pct\CodeTemplate');

		$mockTemplateParser
			->shouldReceive('parse')
			->with(typeOf('string'))
			->andReturn($mockTemplate);
		$mockTemplate
			->shouldReceive('save')
			->with(typeOf('string'), typeOf('array'));

		$resourceCompiler = new ResourceCompiler($mockTemplateParser);
		$resourceCompiler->compile(
			__DIR__ . '/mock-site/resources/sample.tmpl.js',
			__DIR__ . '/target'
		);
	}
}
