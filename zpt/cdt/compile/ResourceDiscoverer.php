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
namespace zpt\cdt\compile;

use \zpt\util\file\FileLister;

/**
 * This class discovers all of the files included in a resource group.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceDiscoverer {

  private $_fileLister;

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

  /**
   * Setter for the FileLister implementation that lists the contents of a
   * given directory.
   */
  public function setFileLister(FileLister $fileLister) {
    $this->_fileLister = $fileLister;
  }

  private function _discoverGroup($group) {
    $ordered = array();

    $scripts = $this->_fileLister->matchesInDirectory($this->_resourceDir,
      "$group-*.$this->_ext");

    $subGroups = $this->_fileLister->matchesInDirectory($this->_resourceDir,
      "$group.*-*.$this->_ext");

    // Check for a setup file.  This file contains anything that is depended on
    // by other files in the group.
    $setupPath = "$group-__setup.$this->_ext";
    $setupIdx = array_search($setupPath, $scripts);
    if ($setupIdx !== false) {
      $ordered[] = $setupPath;
      unset($scripts[$setupIdx]);
    }

    // Add all subgroup files
    // TODO - Do this recursively so that subgroups can also have setup and init
    // scripts and subgroups of their own
    sort($subGroups);
    foreach ($subGroups as $subGroup) {
      $ordered[] = $subGroup;
    }

    // Add all group files
    sort($scripts);
    foreach ($scripts as $script) {
      $ordered[] = $script;
    }

    // Check for initialization file.  This file is the name of the group with
    // the appriopriate description.  It is included last in order to perform
    // any initialization once the group has been loaded.
    $initPath = "$group.$this->_ext";
    if ($this->_fileLister->directoryContains($this->_resourceDir, $initPath)) {
      $ordered[] = $initPath;
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
