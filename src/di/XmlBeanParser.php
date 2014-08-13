<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\di;

use SimpleXMLElement;

/**
 * Parser for beans defined in XML.
 *
 * TODO - Enforce XML structure through the use of an XSL file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class XmlBeanParser
{

	public function parseFile($path) {
		$xml = simplexml_load_file($path, 'SimpleXMLElement',
			LIBXML_NOCDATA);
		return $this->parseXml($xml);
	}

	public function parseXml(SimpleXMLElement $xml) {
		foreach ($xml->bean as $beanDef) {
			// Parse any annotation configuration or marker interfaces before
			// applying XML configuration
			$bean = DependencyParser::parse(
				(string) $beanDef['id'],
				(string) $beanDef['class']
			);

			if (isset($beanDef['initMethod'])) {
				$bean['init'] = (string) $beanDef['initMethod'];
			}

			$props = $this->parseXmlProperties($beanDef);
			$bean['props'] = array_merge($bean['props'], $props);

			$ctorArgs = $this->parseXmlCtorArgs($beanDef);
			$bean['ctor'] = $ctorArgs;

			$beans[] = $bean;
		}

		return $beans;
	}

	private function parseXmlCtorArgs(SimpleXMLElement $bean) {
		$ctorArgs = [];
		if (isset($bean->ctorArg)) {
			foreach ($bean->ctorArg as $arg) {
				if (isset($arg['value'])) {
					$ctorArgs[] = $this->getScalar((string) $arg['value'], true);
				} else if (isset($arg['ref'])) {
					// TODO Eliminate '$' which is currently required for template
					// resolution.
					$ctorArgs[] = '$' . ((string) $arg['ref']);
				}
			}
		}
		return $ctorArgs;
	}

	private function parseXmlProperties(SimpleXMLElement $bean) {
		$props = [];
		if (isset($bean->property)) {
			foreach ($bean->property as $propDef) {
				$prop = [ 'name' => (string) $propDef['name'] ];

				if (isset($propDef['value'])) {
					$prop['val'] = $this->getScalar((string) $propDef['value']);
				} else if (isset($propDef['ref'])) {
					$prop['ref'] = (string) $propDef['ref'];
				} else if (isset($propDef['type'])) {
					$prop['type'] = (string) $propDef['type'];
				}

				$props[] = $prop;
			}
		}
		return $props;
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
