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
 * Parser for the names of any beans on which a given class depends.
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

      $beanId = ltrim($prop->getName(), '_');

      $setter = 'set' . ucfirst($beanId);

      // Make sure that there is a setter for this property
      if (!$classDef->hasMethod($setter)) {
        throw new Exception("Injection property does not have a setter: $beanId");
      }

      if (isset($annos['collection'])) {
        $method = $classDef->getMethod($setter);
        $params = $method->getParameters();
        if (count($params) < 1 || !$params[0]->isArray()) {
          throw new Exception("Collection setters must accept an array");
        }
        $beans[] = array(
          'id'     => $beanId,
          'lookup' => 'byType',
          'type'   => $annos['collection']
        );
      } else {
        $beans[] = array( 'id' => $beanId, 'lookup' => 'byId' );
      }
    }

    return $beans;
  }
}
