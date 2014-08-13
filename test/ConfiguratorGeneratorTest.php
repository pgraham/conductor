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

use \zpt\cdt\compile\ConfiguratorGenerator;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests Configurator generation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfiguratorGeneratorTest extends TestCase {

	protected function setUp() {
		$target = __DIR__ . '/target';
		if (file_exists($target)) {
			exec("rm -r $target");
		}
	}

	public function testConfiguratorGeneration() {
		$xml = <<<XML
<?xml version="1.0" standalone="yes"?>
<conductor>
	<title>My Test Config</title>
	<namespace>mytest</namespace>

	<db>
		<driver>mysql</driver>
		<host>localhost</host>
		<username>local_user</username>
		<password><![CDATA[local_password]]></password>
		<schema>my_schema</schema>
	</db>

	<webRoot>/mytest</webRoot>

</conductor>
XML;
		$xmlCfg = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$generator = new ConfiguratorGenerator(__DIR__, 'dev', $xmlCfg);
		$generator->generate('Configurator');

		$configurator = new \zpt\dyn\Configurator();

		$config = $configurator->getConfig();

		$this->assertInternalType('array', $config['pathInfo']);
		$pathInfo = $config['pathInfo'];
		$this->assertEquals(__DIR__, $pathInfo['root']);
		$this->assertEquals('/mytest', $pathInfo['webRoot']);
		$this->assertEquals(__DIR__ . '/htdocs', $pathInfo['htdocs']);
		$this->assertEquals(__DIR__ . '/lib', $pathInfo['lib']);
		$this->assertEquals(__DIR__ . '/src', $pathInfo['src']);
		$this->assertEquals(__DIR__ . '/target', $pathInfo['target']);

		$this->assertInternalType('array', $config['db']);
		$dbConfig = $config['db'];
		$this->assertEquals('local_user', $dbConfig['username']);
		$this->assertEquals('local_password', $dbConfig['password']);
		$this->assertEquals('my_schema', $dbConfig['schema']);
		$this->assertEquals('mysql', $dbConfig['driver']);
		$this->assertEquals('localhost', $dbConfig['host']);

		$this->assertEquals('dev', $config['env']);
		$this->assertEquals('', $config['logDir']);

		$this->assertEquals('/mytest/', _P('/'));
		$this->assertEquals('/', _AbsP('/mytest/'));
		$this->assertEquals('/mytest', _fsToWeb(__DIR__ . '/htdocs'));
		$this->assertEquals(__DIR__ . '/htdocs', _webToFs('/mytest'));
	}
}
