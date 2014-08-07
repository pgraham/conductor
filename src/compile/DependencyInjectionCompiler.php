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

	private $files = array();
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

	public function addFile($file) {
		if (file_exists($file)) {
			$this->files[] = $file;
		}

		$cfg = simplexml_load_file($file, 'SimpleXMLElement',
			LIBXML_NOCDATA);

		foreach ($cfg->bean as $beanDef) {
			$bean['id'] = $beanDef['id'];
			$bean['class'] = $beanDef['class'];

			// Parse any annotation configuration or marker interfaces before
			// applying XML configuration
			$bean = DependencyParser::parse(
				(string) $beanDef['id'],
				(string) $beanDef['class']
			);

			if (isset($beanDef['initMethod'])) {
				$bean['init'] = (string) $beanDef['initMethod'];
			}

			$props = array();
			if (isset($beanDef->property)) {
				$propDefs = $beanDef->property;

				foreach ($propDefs as $propDef) {
					$prop = array();
					$prop['name'] = (string) $propDef['name'];

					if (isset($propDef['value'])) {
						$prop['val'] = $this->getScalar((string) $propDef['value']);
					} else if (isset($propDef['ref'])) {
						$prop['ref'] = (string) $propDef['ref'];
					} else if (isset($propDef['type'])) {
						$prop['type'] = (string) $propDef['type'];
					} else {
						// TODO Warn about an invalid bean definition
					}
					$props[] = $prop;
				}
			}
			$bean['props'] = array_merge($bean['props'], $props);

			$ctorArgs = array();
			if (isset($beanDef->ctorArg)) {
				$ctor = $beanDef->ctorArg;

				// TODO Is order guaranteed by XML parser?
				// TODO Combine this with logic in dependency parser to eliminate
				//			duplication
				foreach ($ctor as $arg) {
					if (isset($arg['value'])) {
						$ctorArgs[] = $this->getScalar((string) $arg['value'], true);
					} else if (isset($arg['ref'])) {
						$ctorArgs[] = '$' . ((string) $arg['ref']);
					} else {
						// TODO Warn about an invalid bean definition
					}
				}
			}
			$bean['ctor'] = $ctorArgs;

			$this->beans[] = $bean;
		}
	}

	public function compile(RuntimeConfig $config) {
		$dynTarget = $config->getDynamicClassTarget();

		// Build the InjectionConfiguration script
		$srcPath = __DIR__ . '/InjectionConfigurator.tmpl.php';
		$outPath = $dynTarget->getPath()->pathJoin('InjectionConfigurator.php');
		$values = array(
			'namespace' => $dynTarget->getPrefix()->rtrim('\\')->__toString(),
			'beans' => $this->beans
		);
		$tmplResolver = new TemplateResolver($this->tmplParser);
		$tmplResolver->resolve($srcPath, $outPath, $values);
	}

	private function getScalar($val, $quoteStrings = false)
	{
			if (is_numeric($val)) {
					return (float) $val;
			} else if (strtolower($val) === 'true') {
					return true;
			} else if (strtolower($val) === 'false') {
					return false;
			} else if (strtolower($val) === 'null') {
					return null;
			}
			return $quoteStrings ? "'" . $val . "'" : $val;
	}
}
