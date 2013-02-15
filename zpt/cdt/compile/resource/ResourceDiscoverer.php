<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\compile\resource;

use \zpt\util\file\FileLister;
use \DirectoryIterator;

/**
 * This class discovers all of the files included in a resource group.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceDiscoverer {

  private $_resourceDir;
  private $_ext;

  public function __construct($resourceDir, $ext) {
    $this->_resourceDir = $resourceDir;
    $this->_ext = $ext;
  }

  /**
   * Discover all of the files that create the given group, or groups.
   *
   * @param string|array $groups Either the name of a single group to discover
   *   or an array of group names.
   */
  public function discover($groups) {
    if (is_array($groups)) {
      return $this->_discoverGroups($groups);
    } else {
      return $this->_discoverGroup($groups);
    }
  }

  private function _discoverGroup($group) {
    $ordered = array();

    $subGroups = array();
    $files = array();

    $setup = null;
    $init = null;

    $groupParts = explode('.', $group);
    $groupBaseName = array_pop($groupParts);
    $groupBasePath = str_replace('.', '/', $group);
    $groupPath = "$this->_resourceDir/$groupBasePath";
    if (!file_exists($groupPath)) {
      if (file_exists("$groupPath.$this->_ext")) {
        return array("$groupBasePath.$this->_ext");
      } else {
        return array();
      }
    }

    $groupDir = new DirectoryIterator($groupPath);
    foreach ($groupDir as $f) {

      if ($f->isDot()) {
        continue;
      }

      if ($f->isDir()) {
        $subGroup = $f->getBasename();
        $subGroups[$subGroup] = $this->_discoverGroup("$group.$subGroup");

      } else {
        $fname = $f->getBasename();
        if (pathinfo($fname, PATHINFO_EXTENSION) === $this->_ext) {
          
          if ($fname === "__init.$this->_ext") {
            $setup = $fname;

          } else if ($fname === "$groupBaseName.$this->_ext") {
            $init = $fname;

          } else {
            $files[] = $fname;
          }
        }
      }
    }

    if ($setup !== null) {
      $ordered[] = "$groupBasePath/$setup";
    }
    ksort($subGroups);
    foreach ($subGroups as $name => $subGroup) {
      // Due to recursion, the filename should already be prepended with the
      // groups base path
      $ordered = array_merge($ordered, $subGroup);
    }

    foreach ($files as $file) {
      $ordered[] = "$groupBasePath/$file";
    }

    if ($init !== null) {
      $ordered[] = "$groupBasePath/$init";
    }

    return $ordered;
  }

  private function _discoverGroups($groups) {
    $scripts = array();
    foreach ($groups as $group) {
      $scripts = array_merge($scripts, $this->_discoverGroup($group));
    }
    return $scripts;
  }
}
