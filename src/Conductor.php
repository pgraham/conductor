<?php
namespace Conductor;

class Conductor {

  public static function loadLibs($reedSrc, $oboeSrc = null, $bassoonSrc = null)
  {
    // Include the autoloader
    require_once __DIR__ . '/Autoloader.php';

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

  public static function init(\Reed_Config $config = null) {
    // If provided, set the config class
    if ($config !== null) {
      \Reed_Config::setConfig($config);
    }
  }

  public static function setPageTemplate(Template $template) {
    Page::setTemplate($template);
  }
}
