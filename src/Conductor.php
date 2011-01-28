<?php
namespace Conductor;

class Conductor {

  /**
   * Initialize the framework.  The configuration options are given as a
   * key-value array.
   *
   * Options:
   * --------
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
   * reedconfig
   *   Array with two element:
   *     path
   *       The path to a file that contains a custom Reed configuration class.
   *     class
   *       The name of a custom Reed configuration class to be loaded.
   *   If the name of the class is 'Config' then just specifying the path is
   *   sufficient
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
  {
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
      require_once $reedSrc . '/
    // Include reed
    require_once $reedSrc . '/Reed/Autoloader.php';

    // If provided, include optional libraries
    if ($oboeSrc !== null) {
      require_once $oboeSrc . '/Autoloader.php';
    }

    if ($bassoonSrc !== null) {
      require_once $bassoonSrc . '/Bassoon/Autoloader.php';
    }
  }

    // If provided, set the config class
    if ($config !== null) {
      \Reed_Config::setConfig($config);
    }
  }

  public static function setPageTemplate(Template $template) {
    Page::setTemplate($template);
  }
}
