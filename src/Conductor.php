<?php
namespace Conductor;

class Conductor {

  public static function init($reedSrc, $oboeSrc = null, $bassoonSrc = null) {
    // Include the autoloader
    require_once __DIR__ . '/Autoloader.php';

    require_once $reedSrc . '/Reed/Autoloader.php';
    require_once $oboeSrc . '/Oboe/Autoloader.php';
    require_once $bassoonSrc . '/Bassoon/Autoloader.php';
  }

  public static function setPageTemplate(Template template) {
    Page::setTemplate(Template $template);
  }
}
