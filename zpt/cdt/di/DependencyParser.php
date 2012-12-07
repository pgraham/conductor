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
namespace zpt\cdt\di;

use \zeptech\anno\Annotations;
use \Exception;
use \ReflectionClass;

/**
 * Parser for annotation configured bean dependencies.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyParser {

  public static function parse($classDef) {
    if (is_string($classDef)) {
      $classDef = new ReflectionClass($classDef);
    }

    $beans = array();

    $properties = $classDef->getProperties();
    foreach ($properties as $prop) {
      $annos = new Annotations($prop);
      if (!isset($annos['injected'])) {
        continue;
      }

      $propertyName = ltrim($prop->getName(), '_');

      // Make sure that there is a setter for this property
      $setter = 'set' . ucfirst($propertyName);
      if (!$classDef->hasMethod($setter)) {
        throw new Exception("Injection property does not have a setter " .
          "($setter): Tried to inject `$beanId` into `" .
          $classDef->getName() . "`.");
      }

      if (isset($annos['collection'])) {
        $method = $classDef->getMethod($setter);
        $params = $method->getParameters();
        if (count($params) < 1 || !$params[0]->isArray()) {
          throw new Exception("Collection setters must accept an array");
        }
        $beans[] = array(
          'name' => $propertyName,
          'type' => $annos['collection']
        );
      } else {
        $beanId = isset($annos['injected']['ref'])
          ? $annos['injected']['ref']
          : $propertyName;

        $beans[] = array(
          'name' => $propertyName,
          'ref'   => $beanId,
        );
      }

    }

    return $beans;
  }
}
