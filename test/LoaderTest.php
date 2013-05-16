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
 */
namespace zpt\cdt\test;

use \PHPUnit_Framework_TestCase as TestCase;

use \ComposerLoaderContainer;
use \zpt\anno\Annotations;
use \zpt\cdt\Loader;
use \zpt\oobo\Element;
use \zpt\opal\CompanionLoader;
use \zpt\orm\Criteria;
use \zpt\rest\RestServer;
use \zpt\util\String;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the dependency loader class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LoaderTest extends TestCase {

	public function testLoader() {
		Loader::registerDependencies(
			__DIR__ . '/..',
			ComposerLoaderContainer::$loader
		);

		// Load a class from each dependency
		$str = new String("test");
		$this->assertInstanceOf('zpt\util\String', $str);

		$anno = new Annotations();
		$this->assertInstanceOf('zpt\anno\Annotations', $anno);

		$div = Element::div();
		$this->assertInstanceOf('zpt\oobo\Div', $div);

		$companionLoader = new CompanionLoader();
		$this->assertInstanceOf('zpt\opal\CompanionLoader', $companionLoader);

		$criteria = new Criteria();
		$this->assertInstanceOf('zpt\orm\Criteria', $criteria);

		$restServer = new RestServer();
		$this->assertInstanceOf('zpt\rest\RestServer', $restServer);
	}
}
