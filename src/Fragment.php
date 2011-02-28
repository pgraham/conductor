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
 * @package conductor
 */
namespace conductor;

use \Exception;

use \oboe\item;

/**
 * This class pulls an HTML fragment from a file and wraps it as a document
 * item.  A given array of values can be used to substitute variables in the
 * fragment.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Fragment implements item\Body {

  /* The fragment */
  private $_fragment;

  public function __construct($path, array $values = array()) {
    if (!file_exists($path)) {
      throw new Exception("Unable to load fragment: Path not found: $path");
    }

    $this->_fragment = file_get_contents($path);

    // _flattenArray transforms nested array into top level keys in the form
    // level1.level2.level3.  So any array defined as
    // array(
    //   'level1' => array(
    //     'level2' => array(
    //       'level3' => 'level3val'
    //     ),
    //     'level2val => 'level2val'
    //   ),
    //   'level1val' => 'level1val'
    // );
    //
    // would be transformed to the following:
    // array(
    //   'level1.level2.level3' => 'level3val',
    //   'level1.level2val      => 'level2val',
    //   'level1val'            => 'level1val'
    // ); 
    $flat = $this->_flattenArray($values);
    $this->_fragment = str_replace(array_keys($flat), array_values($flat),
      $this->_fragment);
  }

  public function __toString() {
    return $this->_fragment;
  }

  public function addToBody() {
    \oboe\Page::addElementToBody($this);
  }

  private function _flattenArray(array $toFlatten, $prefix = null) {
    $flat = array();

    foreach ($toFlatten as $key => $val) {
      if ($prefix === null) {
        $keyPrefix = $key;
      } else {
        $keyPrefix = $prefix . '.' . $key;
      }

      if (is_array($val)) {
        $subArray = $this->_flattenArray($val, $keyPrefix);
        $flat = array_merge($flat, $subArray);
      } else {
        $flat['${' . $keyPrefix . '}'] = $val;
      }
    }
    return $flat;
  }
}
