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

  public function discover($groups) {
    if (is_array($groups)) {
      return $this->_discoverGroups($groups);
    } else {
      return $this->_discoverGroup($groups);
    }
  }

  private function _discoverGroup($group) {
    $scripts = glob("$this->_resourceDir/$group-*.$this->_ext");

    // See if a script with the group name and no suffix exists.  This script
    // is added last so any initialization code that relies on the content
    // of the scripts in the group should go in this script.
    $initScriptPath = "$this->_resourceDir/$group.$this->_ext";
    if (file_exists($initScriptPath)) {
      $scripts[] = $initScriptPath;
    }

    return $scripts;
  }

  private function _discoverGroups($groups) {
    $scripts = array();
    foreach ($groups as $group) {
      $scripts = array_merge($scripts, $this->_discoverGroup($group));
    }
    return $scripts;
  }
}
