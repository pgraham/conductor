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

use \zpt\anno\Annotations;
use \Exception;
use \ReflectionClass;

/**
 * Parser for annotation configured bean dependencies.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyParser {

  /**
   * Parse the specified class for DI information.
   *
   * @param string $id The id to use for the bean definition.
   * @param string $classDef The class to parse.
   * @return array
   */
  public static function parse($id, $classDef) {
    if (is_string($classDef)) {
      $classDef = new ReflectionClass($classDef);
    }

    $bean = array(
      'id' => $id,
      'class' => $classDef->getName()
    );

    if ($classDef->implementsInterface('zpt\cdt\di\InitializingBean')) {
      $bean['init'] = 'init';
    }

    $beanProps = array();
    $parent = $classDef;
    while ($parent) {
      $beanProps = array_merge($beanProps, self::parseClassProperties($parent));
      $parent = $parent->getParentClass();
    }

    $bean['props'] = $beanProps;

    $ctorArgs = self::parseConstructorArgs($classDef);
    $bean['ctor'] = $ctorArgs;

    return $bean;
  }

  public static function parseClassProperties($classDef) {
    $properties = $classDef->getProperties();

    $beanProps = array();
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
          "($setter): Tried to inject `$propertyName` into `" .
          $classDef->getName() . "`.");
      }

      if (isset($annos['collection'])) {
        $method = $classDef->getMethod($setter);
        $params = $method->getParameters();
        if (count($params) < 1 || !$params[0]->isArray()) {
          throw new Exception("Collection setters must accept an array");
        }
        $beanProps[] = array(
          'name' => $propertyName,
          'type' => $annos['collection']
        );
      } else {
        $beanId = isset($annos['injected']['ref'])
          ? $annos['injected']['ref']
          : $propertyName;

        $beanProps[] = array(
          'name' => $propertyName,
          'ref'   => $beanId,
        );
      }
    }

    return $beanProps;
  }

  public static function parseConstructorArgs($classDef) {
    $ctor = $classDef->getConstructor();
    if ($ctor === null) {
      return array();
    }

    $annos = new Annotations($ctor->getDocComment());
    if (!isset($annos['ctorArg'])) {
      return array();
    }

    $args = array();
    foreach ($annos->asArray('ctorArg') as $ctorArg) {
      if (isset($ctorArg['value'])) {
        $args[] = $ctorArg['value'];
      } else if (isset($ctorArg['ref'])) {
        $args[] = '$' . $ctorArg['ref'];
      } else {
        // TODO Warn about invalid ctorArg annotations
      }
    }
    return $args;
  }
}
