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
namespace zpt\cdt {

/**
 * This class provides localized content.  If no language file is present for
 * the given language then the default language is used but strings will be
 * accented to indicated that they have not been localized.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class L10N {

  /** Dynamically created function for retrieving a requested string */
  private static $_getFn;

  public static function get($key) {
    $getFn = self::$_getFn;
    return $getFn($key);
  }

  /**
   * Load the specified language.
   *
   * @param string $lang The language to load
   * @param string $target The target path for the site.  Compiled language
   *   files are resolved relative to this path
   */
  public static function load($lang, $target) {
    $langFilePath = "$target/i18n/$lang.strings.php";
    if (!file_exists($langFilePath)) {
      self::$_getFn = function ($key) {
        return "!!!!!!!! $key !!!!!!!!";
      };
      return;
    }

    require $langFilePath;
    self::$_getFn = function ($key) {
      global $L10N;
      if (!isset($L10N[$key])) {
        return "XXXXXXXX $key XXXXXXXX";
      }
      return $L10N[$key];
    };
  }
} 

} // End namespace

namespace { // global namespace
  /** Alias for L10N::get($key) */
  function _L($key) {
    return \zpt\cdt\L10N::get($key);
  }
}
