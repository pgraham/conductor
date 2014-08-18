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
				$ctorArgs[] = $this->parseProperty($arg);
			}
		}
		return $ctorArgs;
	}

	private function parseXmlProperties(SimpleXMLElement $bean) {
		$props = [];
		if (isset($bean->property)) {
			foreach ($bean->property as $prop) {
				$props[] = $this->parseProperty($prop);
			}
		}
		return $props;
	}

	private function parseProperty(SimpleXMLElement $propDef) {
		$prop = [ 'name' => (string) $propDef['name'] ];

		if (isset($propDef['value'])) {
			$prop['val'] = DependencyParser::parseScalar((string) $propDef['value']);
		} else if (isset($propDef['ref'])) {
			$prop['ref'] = (string) $propDef['ref'];
		} else if (isset($propDef['type'])) {
			$prop['type'] = (string) $propDef['type'];
		} else if (isset($propDef->map)) {
			$prop['val'] = DependencyParser::parseMap($propDef->map);
		}
		return $prop;
	}
}
