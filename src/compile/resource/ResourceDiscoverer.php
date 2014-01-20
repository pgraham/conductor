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
   * Discovered files are ordered according to the following rules.  All files 
   * mentioned in the ordering rules are assumed to have the appropriate 
   * extension for the resource type. So when discovering CSS resources `__init` 
   * really refers to `__init.css`.
   *
   *  1.  If it exists, a file named either `__setup` or `__init` will be
   *      combined first. If both exist `__init` will be added first.
   *
   *  2.  This is followed by any subgroups.  Sub groups are simply directories 
   *      within the group directory. The ordering process is recursive so sub 
   *      groups will follow the same ordering rules and can themselves contain 
   *      subgroups.
   *
   *  3.  If it exists subgroups will be followed by a script named
   *      `__post-sub`.
   *
   *  4.  This will followed by all files with the resource type extension 
   *      except for a file that has the same name as the base name of the
   *      group.
   *
   *  5.  If it a exists, a file with the same name as the base name of the 
   *      group and the appropriate resource extension.
   *
   *  6.  Subgroups and normal resource files will be ordered alphabetically by 
   *      their basename. N.B. This is currently done with a SORT_TYPE_REGULAR
   *      to the PHP function sort and ksort, however this should not be relied
   *      on *at this point* as the rules surrounding this ordering will likely
   *      be revised in a later release.
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

    $setup = array();
    $postSub = null;
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
            $setup['init'] = $fname;
          } else if ($fname === "__setup.$this->_ext") {
            $setup['setup'] = $fname;
          } else if ($fname === "__post-sub.$this->_ext") {
            $postSub = $fname;
          } else if ($fname === "$groupBaseName.$this->_ext") {
            $init = $fname;
          } else {
            $files[] = $fname;
          }
        }
      }
    }

    if (array_key_exists('init', $setup)) {
      $ordered[] = "$groupBasePath/$setup[init]";
    }
    if (array_key_exists('setup', $setup)) {
      $ordered[] = "$groupBasePath/$setup[setup]";
    }
    ksort($subGroups);
    foreach ($subGroups as $name => $subGroup) {
      // Due to recursion, the filename should already be prepended with the
      // groups base path
      $ordered = array_merge($ordered, $subGroup);
    }

    if ($postSub !== null) {
      $ordered[] = "$groupBasePath/$postSub";
    }

    sort($files);
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
