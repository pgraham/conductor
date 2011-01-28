<?php
namespace Conductor;
use \Reed\Config;

class Conductor {

  private static $_initialized = false;

  public static function loadLibs($reedSrc, $oboeSrc = null, $bassoonSrc = null)
  {
    // Include the autoloader
    require_once __DIR__ . '/Autoloader.php';

    // Include reed
    require_once $reedSrc . '/Autoloader.php';

    // If provided, include optional libraries
    if ($oboeSrc !== null) {
      require_once $oboeSrc . '/Autoloader.php';
    }

    if ($bassoonSrc !== null) {
      require_once $bassoonSrc . '/Autoloader.php';
    }
  }

  public static function init(Config $config = null) {
    if (self::$_initialized) {
      // TODO - Give a warning if DEBUG is defined and set to true
      return;
    }
    self::$_initialized = true;

    // If provided, set the config class
    if ($config !== null) {
      Config::setConfig($config);
    }
  }

  public static function setPageTemplate(Template $template) {
    self::_initialize();
    Page::setTemplate($template);
  }

  private static function _initialize() {
    if (self::$_initialized) {
      return;
    }
    self::_init();
  }
}
