<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\di;

use ReflectionClass;

/**
 * Dependency injection container.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Container
{

	private $named = [];
	private $byType = [];

	public function addComponent($arg1, $arg2 = null) {
		if ($arg2 !== null) {
			$name = $arg1;
			$cmp = $arg2;

			$this->named[$name] = $cmp;
		} else {
			$cmp = $arg1;
		}

		if (is_object($cmp)) {
			$this->addComponentByType($cmp);
		}
	}

	public function getComponent($name) {
		if (isset($this->named[$name])) {
			return $this->named[$name];
		}
		return null;
	}

	public function getComponentOfType($type) {
		$type = strtolower($type);
		if (isset($this->byType[$type])) {
			return $this->byType[$type][0];
		}
		return null;
	}

	public function getComponentsOfType($type) {
		$type = strtolower($type);
		if (isset($this->byType[$type])) {
			return $this->byType[$type];
		}
		return [];
	}

	public function hasComponent($name) {
		return isset($this->named[$name]);
	}

	public function hasComponentOfType($type) {
		return isset($this->byType[strtolower($type)]);
	}

	private function addComponentByType($cmp) {
		$refClass = new ReflectionClass($cmp);

		$interfaces = $refClass->getInterfaces();

		foreach ($interfaces as $interfaceName => $interface) {
			$this->addComponentByTypeInheritance($cmp, $interface);
		}

		$this->addComponentByTypeInheritance($cmp, $refClass);
	}

	private function addComponentByTypeInheritance($cmp, $refClass) {
		while ($refClass) {
			$this->addComponentToTypeContainer($cmp, $refClass->getName());
			$refClass = $refClass->getParentClass();
		}
	}

	private function addComponentToTypeContainer($cmp, $type) {
		$type = strtolower($type);

		if (!isset($this->byType[$type])) {
			$this->byType[$type] = [ $cmp ];
		} else {
			$this->byType[$type][] = $cmp;
		}
	}
}
