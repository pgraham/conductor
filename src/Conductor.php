<?php
namespace Conductor;
use \Reed\Config;

class Conductor {

  private static $_initialized = false;

  /**
   * Initialize the framework.  This consists of registering the autoloaders for
   * the libraries for which paths have been provided.
   *
   * The configuration options are given as a key-value array.
   *
   * Options:
   * --------
   *
   * libpath
   *   Base path for Reed, Oboe, Bassoon and Clarinet.  This is a
   *   shortcut to providing individual paths.  The individual libraries will be
   *   assumed to be in a sub directory of the given path with a lower cased
   *   name of the library.  This option can be overridden for individual
   *   libraries by specifying a libraries path as part of the configuration.
   *
   * reedpath
   *   Base path for Reed
   *
   * oboepath
   *   Base path for Oboe
   *
   * bassoonpath
   *   Base path for Bassoon
   *
   * clarinetpath
   *   Base path for Clarinet
   *
   * clarinetconfig
   *   Configuration array to be passed to clarinet's intialization function
   */
  public static function init($config = array()) {
    if (self::$_initialized) {
      // TODO - Give a warning if DEBUG is defined and set to true
      return;
    }
    self::$_initialized = true;

    // Include the clarinet autoloader
    require_once __DIR__ . '/Autoloader.php';

    $libPath = (isset($config['libpath']) ? $config['libpath'] : null;
    $reedPath = (isset($config['reedpath'])) ? $config['reedpath'] : null;
    $oboePath = (isset($config['oboepath'])) ? $config['oboepath'] : null;
    $bassoonPath = (isset($config['bassoonpath']))
      ? $config['bassoonpath']
      : null;
    $clarinetPath = (isset($config['clarinetpath']))
      ? $config['clarinetpath']
      : null;

    if ($reedPath !== null) {
      require_once $reedPath . '/src/Autoloader.php';
    } else if ($libPath !== null) {
      require_once $libPath . '/reed/src/Autoloader.php';
    }

    if ($oboePath !== null) {
      require_once $oboePath .'/src/Autoloader.php';
    } else if ($libPath !== null) {
      require_once $libPath . '/oboe/src/Autoloader.php';
    }

    if ($bassoonSrc !== null) {
      require_once $bassoonSrc . '/src/Autoloader.php';
    } else if ($libPath !== null) {
      require_once $libPath . '/bassoon/src/Autoloader.php';
    }

    $clarinetLoaded = false;
    if ($clarinetSrc !== null) {
      require_once $clarinetSrc . '/src/Autoloader.php';
      $clarinetLoaded = true;
    } else if ($libPath !== null) {
      require_once $libPath . '/clarinet/src/Autoloader.php';
      $clarinetLoaded = true;
    }

    if (isset($config['clarinetconfig'])) {
      if ($clarinetLoaded) {
        Clarinet::init($config['clarinetconfig']);
      } else {
        // TODO - Give warning if debug is defined
      }
  }

  public static function setConfig(Config $config) {
    if (!self::$_intialized) {
      throw new Exception('Conductor has not yet been initialized');
    }

    Config::setConfig($config);
  }

  public static function setPageTemplate(Template $template) {
    if (!self::$_intialized) {
      throw new Exception('Conductor has not yet been initialized');
    }

    Page::setTemplate($template);
  }
}
