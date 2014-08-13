<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\compile;

use zpt\cdt\config\RuntimeConfig;
use zpt\cdt\di\DependencyParser;
use zpt\cdt\di\XmlBeanParser;
use zpt\cdt\i18n\ModelMessages;
use zpt\orm\actor\QueryBuilder;
use zpt\orm\companion\PersisterGenerator;
use zpt\orm\companion\TransformerGenerator;
use zpt\orm\companion\ValidatorGenerator;
use zpt\pct\CodeTemplateParser;
use zpt\pct\TemplateResolver;

/**
 * This class compiles a script which initializes a dependency injection
 * container.
 *
 * Beans are parsed from XML files defined by conductor, any installed modules
 * and the site. Some beans are also added programatically by other compilers in
 * the compilation process.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyInjectionCompiler implements Compiler
{

	private $beans = array();

	private $tmplParser;

	public function __construct(CodeTemplateParser $tmplParser = null) {
		if ($tmplParser === null) {
			$tmplParser = new CodeTemplateParser();
		}
		$this->tmplParser = $tmplParser;
	}

	public function addBean($id, $class, $props = array()) {
		$bean = DependencyParser::parse($id, $class);

		// Merge annotation configured beans with spefied bean property values.
		// Specified property values override annotation configuration.
		$bean['props'] = array_merge(
			$bean['props'],
			$props
		);

		$this->beans[] = $bean;
	}

	public function compile(RuntimeConfig $config) {
		// Resolve dependency XML files
		$xmlFiles = $this->findDependencyXmls($config->getPathInfo());
		$xmlBeans = $this->parseDependencyXmls($xmlFiles);

		// Make sure dynamically defined beans override any xml defined beans with
		// the same name
		$beans = array_merge($xmlBeans, $this->beans);

		$srcPath = __DIR__ . '/InjectionConfigurator.tmpl.php';
		$dynTarget = $config->getDynamicClassTarget();
		$outPath = $dynTarget->getPath()->pathJoin('InjectionConfigurator.php');
		$values = array(
			'namespace' => $dynTarget->getPrefix()->rtrim('\\')->__toString(),
			'beans' => $beans
		);
		$tmplResolver = new TemplateResolver($this->tmplParser);
		$tmplResolver->resolve($srcPath, $outPath, $values);
	}

	private function findDependencyXmls($pathInfo) {
		$files = [ "$pathInfo[cdtRoot]/resources/dependencies.xml" ];

		$moduleIterator = new ModuleIterator($pathInfo['modules']);
		foreach ($moduleIterator as $modulePath) {
			$diPath = "$modulePath/resources/dependencies.xml";
			if (file_exists($diPath)) {
				$files[] = $diPath;
			}
		}

		$siteDiPath = "$pathInfo[src]/resources/dependencies.xml";
		if (file_exists($siteDiPath)) {
			$files[] = $diPath;
		}
		return $files;
	}

	private function parseDependencyXmls(array $files) {
		$xmlBeanParser = new XmlBeanParser();
		$xmlBeans = [];
		foreach ($files as $file) {
			$fileBeans = $xmlBeanParser->parseFile($file);
			$xmlBeans = array_merge($xmlBeans, $fileBeans);
		}

		return $xmlBeans;
	}
}
