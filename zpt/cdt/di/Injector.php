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

/**
 * Dependency injection container.  This is just basically a wrapper for a
 * hashmap.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Injector {

  private static $_beans = array();

  /**
   * Add a bean to the container.
   */
  public static function addBean($id, $bean) {
    self::$_beans[$id] = $bean;
  }

  /**
   * Get the bean with the given ID or null if not found.
   *
   * @return Object
   */
  public static function getBean($id) {
    return self::$_beans[$id];
  }

  /**
   * Get an array containing all beans with the given type.
   *
   * @return array
   */
  public static function getBeans($type) {
    $beans = array();
    foreach (self::$_beans as $bean) {
      if ($bean instanceof $type) {
        $beans[] = $bean;
      }
    }
    return $beans;
  }

  public static function inject($obj, array $beans) {
    foreach ($beans as $bean) {
      $beanId = $bean['id'];
      $setter = "set" . ucfirst($beanId);

      if ($bean['lookup'] === 'byId') {
        $obj->$setter(self::getBean($beanId));
      } else {
        $obj->$setter(self::getBeans($bean['type']));
      }
    }
  }
}
