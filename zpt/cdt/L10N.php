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

use oboe\Element;

/**
 * This class provides localized content.  If no language file is present for
 * the given language then the default language is used but strings will be
 * accented to indicated that they have not been localized.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class L10N {

  /* Map of keys */
  private static $_strs;

  /* The currently loaded language */
  private static $_lang;

  /**
   * Get the localized string with the given key.
   *
   * @param string $key The key of the string to retrieve.
   * @param string $type The type of string to retrieve. Either 'raw' or 'md'.
   *        Default: raw
   * @return string
   */
  public static function get($key, $type = 'raw') {
    $str = self::getStr($key);
    if (is_array($str)) {
      $str = $str[$type];
    }

    $args = func_get_args();
    array_shift($args);
    if (count($args) > 0) {
      array_unshift($args, $str);
      $str = call_user_func_array('zpt\util\StringUtils::format', $args);
    }
    return $str;
  }

  /**
   * Get the currently loaded language.
   *
   * @return string Language identifier.
   */
   public static function getLang() {
     return self::$_lang;
   }

  /**
   * Wrap any text in localized subject matching localized search with anchor
   * tags for the given URL.
   *
   * @param string $subjectId L10N id of the linkify subject
   * @param string $searchId L10N id of the text to wrap within subject
   * @param string $url
   */
  public static function linkify($subjectId, $searchId, $url) {
    $subject = _L($subjectId);
    $search = _L($searchId);

    $re = '/(' . preg_quote($search) . ')/i';
    $split = preg_split($re, $subject, null, PREG_SPLIT_DELIM_CAPTURE);

    $linkified = '';
    foreach ($split as $chunk) {
      if (preg_match($re, $chunk)) {
        $linkified .= Element::a($url, $chunk)->__toString();
      } else {
        $linkified .= $chunk;
      }
    }
    return $linkified;
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
      // Create a get function which returns an string indicating an error.
      self::$_getFn = function ($key) {
        return "!!!!!!!! $key !!!!!!!!";
      };

      return;
    }

    // Save the loaded language and require the generated strings file.
    require $langFilePath;
    self::$_lang = $lang;
    self::$_strs = $GLOBALS['L10N'] ?: array();
  }

  /**
   * Determine if a localized string for the specified key exists.
   *
   * @param string $key
   * @return boolean
   */
  public static function strExists($key) {
    return isset(self::$_strs[$key]);
  }

  private static function getStr($key) {
    if (!isset(self::$_strs[$key])) {
      return "XXXXXXXX $key XXXXXXXX";
    }
    return self::$_strs[$key];
  }

} 

} // End namespace

namespace { // global namespace
  /** Alias for L10N::get($key) */
  function _L($key) {
    return String(\zpt\cdt\L10N::get($key));
  }
}
