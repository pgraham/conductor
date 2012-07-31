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
namespace zpt\cdt\rest;

use \zeptech\anno\Annotations;
use \zpt\cdt\di\DependencyParser;
use \zpt\pct\AbstractGenerator;
use \Exception;
use \ReflectionClass;

/**
 * This class generates zpt\rest\RequestHandler implementations that dispatch
 * service requests to the appropriate method in a Service class instance.
 *
 * Service classes are classes annotated with the @Service class level.
 * annotation.  Methods are annotated with @Method { GET | POST | PUT | DELETE }
 * annotations and any number of @Uri annotations.  This information is used to
 * generate a RequestHandler implementation that delegates requests to the
 * appropriate method.
 *
 * All service methods must accept accept two parameters, a zeptech\rest\Request
 * object and a zeptech\rest\Response object.
 */
class ServiceRequestDispatcher extends AbstractGenerator {

  protected static $actorNamespace = 'zeptech\dynamic\rest';

  protected function getTemplatePath() {
    return __DIR__ . '/ServiceRequestDispatcher.tmpl.php';
  }

  protected function getValues($className) {
    $defClass = new ReflectionClass($className);
    $defAnnos = new Annotations($defClass);
    if (!isset($defAnnos['service'])) {
      throw new Exception("$className is not a service definition");
    }

    $deleteMethods = array();
    $getMethods = array();
    $postMethods = array();
    $putMethods = array();

    $methods = $defClass->getMethods();
    foreach ($methods as $method) {
      $methodAnnos = new Annotations($method);

      if (!isset($methodAnnos['uri']) || !isset($methodAnnos['method'])) {
        continue;
      }

      $methodName = $method->getName();

      $uris = $methodAnnos['uri'];
      if (!is_array($uris) || isset($uris['id'])) {
        $uris = array( $uris );
      }

      $methodDef = array(
        'name' => $methodName
      );

      $httpMethods = $methodAnnos['method'];
      if (!is_array($httpMethods)) {
        $httpMethods = explode(' ', $httpMethods);

        foreach ($httpMethods as $httpMethod) {
          $httpMethod = strtoupper($httpMethod);
          switch ($httpMethod) {
            case 'DELETE':
            $deleteMethods[] = $methodDef;
            break;

            case 'GET':
            $getMethods[] = $methodDef;
            break;

            case 'POST':
            $postMethods[] = $methodDef;
            break;

            case 'PUT':
            $putMethods[] = $methodDef;
            break;

            default:
            assert("false /* Unrecognized HTTP method $httpMethod");
          }
        }
      }

    }

    $values = array(
      'serviceClass' => $className,
      'deleteMethods' => $deleteMethods,
      'getMethods' => $getMethods,
      'postMethods' => $postMethods,
      'putMethods' => $putMethods
    );

    $beans = DependencyParser::parse($defClass);
    if ($beans !== null && count($beans) > 0) {
      $values['beans'] = $beans;
    }

    return $values;
  }

}
