<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\rest;

use \zpt\anno\Annotations;
use \zpt\cdt\di\Injector;
use \zpt\opal\CompanionGenerator;
use \Exception;
use \ReflectionClass;
use \StdClass;

/**
 * This class generates zpt\rest\RequestHandler implementations that dispatch
 * service requests to the appropriate method in a Service class instance.
 *
 * Service classes are classes annotated with the @Service class level.
 * annotation.	Methods are annotated with @Method { GET | POST | PUT | DELETE }
 * annotations and any number of @Uri annotations.	This information is used to
 * generate a RequestHandler implementation that delegates requests to the
 * appropriate method.
 *
 * All service methods must accept accept two parameters, a zpt\rest\Request
 * object and a zpt\rest\Response object.
 */
class ServiceRequestDispatcher extends CompanionGenerator {

	const BEAN_ID_SUFFIX = 'ServiceRequestDispatcher';

	const COMPANION_NAMESPACE = 'zpt\dyn\rest';

	protected function getCompanionNamespace($defClass) {
		return self::COMPANION_NAMESPACE;
	}

	protected function getTemplatePath($defClass) {
		return __DIR__ . '/ServiceRequestDispatcher.tmpl.php';
	}

	protected function getValues($className) {
		$defClass = new ReflectionClass($className);
		$defAnnos = new Annotations($defClass);
		if (!isset($defAnnos['service'])) {
			throw new Exception("$className is not a service definition");
		}

		$mappings = array();
		$deleteMethods = array();
		$getMethods = array();
		$postMethods = array();
		$putMethods = array();

		$methods = $defClass->getMethods();
		foreach ($methods as $method) {
			$methodAnnos = new Annotations($method);
			if (!isset($methodAnnos['uri']) || !isset($methodAnnos['method'])) {
				// This isn't a service method so ignore it.
				continue;
			}

			$methodName = $method->getName();
			$methodDef = array(
				'name' => $methodName,
				'enforceOrder' => false
			);

			// This array will be merged into $methodDef for POST and PUT request
			// methods
			$enforceOrder = array(
				'enforceOrder' => isset($methodAnnos['enforceOrder'])
			);

			$httpMethods = explode(' ', $methodAnnos['method']);
			foreach ($httpMethods as $httpMethod) {

				$mapping = new StdClass();
				$mapping->uri = $methodAnnos['uri'];
				$mapping->method = $httpMethod;
				$mapping->id = $methodName;
				$mappings[] = $mapping;

				$httpMethod = strtoupper($httpMethod);
				switch ($httpMethod) {
					case 'DELETE':
					$deleteMethods[] = $methodDef;
					break;

					case 'GET':
					$getMethods[] = $methodDef;
					break;

					case 'POST':
					$postMethods[] = array_merge($methodDef, $enforceOrder);
					break;

					case 'PUT':
					$putMethods[] = array_merge($methodDef, $enforceOrder);
					break;

					default:
					assert("false /* Unrecognized HTTP method $httpMethod */");
				}
			}
		}

		$values = array(
			'mappings'			=> $mappings,
			'methodTypes'		=> array(
				array(
					'type' => 'delete',
					'methods' => $deleteMethods
				),
				array(
					'type' => 'get',
					'methods' => $getMethods
				),
				array(
					'type' => 'post',
					'methods' => $postMethods
				),
				array(
					'type' => 'put',
					'methods' => $putMethods
				)
			)
		);

		return $values;
	}

}
