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
namespace conductor\admin;

use \reed\reflection\Annotations;
use \reed\String;

/**
 * This class contains functionality for parsing annotations in a ModelView
 * for {@link AdminClient} generation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminViewParser {

  /**
   * Parse the display names from a given set of annotations.
   *
   * @param string $name
   * @param Annotations $annotations
   * @return array with singular display name contained at index 'singular' and
   *   plural display name at index 'plural'.  If either are not defined then
   *   then a default will be determined using the given model, property or
   *   relationship name.
   */
  public static function parseDisplayNames($name, Annotations $annotations) {
    $singular = null;
    $plural = null;

    if (isset($annotations['display']) && is_array($annotations['display'])) {
      if (isset($annotations['display']['name'])) {
        $singular = $annotations['display']['name'];
      }

      if (isset($annotations['display']['plural'])) {
        $plural = $annotations['display']['plural'];
      }
    }

    if ($singular === null) {
      $singular = String::fromCamelCase($name, ' ', String::CAPITALIZE_WORDS);
    }

    if ($plural === null) {
      $plural = $singular . 's';
    }

    return array(
      'singular' => $singular,
      'plural'   => $plural
    );
  }

  /**
   * Parse the display state for the given name and annotations.
   *
   * @param string $name
   * @param Annotations $annotations
   * @param string $default One of the {@link AdminModelInfo}::DISPLAY_*
   *   constants.
   */
  public static function parseDisplayState($name, Annotations $annotations,
    $default = null)
  {

    if ($default === null) {
      $default = AdminModelInfo::DISPLAY_EDIT;
    }

    $display = null;
    if (!isset($annotations['display'])) {
      $display = $default;

    } else if (is_array($annotations['display'])) {

      if (isset($annotations['display']['mode'])) {
        $display = $annotations['display']['mode'];
      }  else {
        $display = $default;
      }

    } else {
      $display = $annotations['display'];
    }

    // TODO Verify value
    return $display;
  }
}
