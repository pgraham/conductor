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
namespace conductor\script;

use \oboe\head\Javascript;

use \reed\generator\CodeTemplateLoader;
use \reed\WebSitePathInfo;

/**
 * This class compiles the conductor client javascript.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Client extends Javascript {

  public function __construct(WebSitePathInfo $pathInfo) {
    $this->_compile($pathInfo);

    $webPath = $pathInfo->getWebAccessibleTarget();
    parent::__construct("$webPath/js/conductor.js");
  }

  private function _compile($pathInfo) {
    if (!defined('DEBUG') || DEBUG !== true) {
      return;
    }

    $webTarget = $pathInfo->getWebTarget();
    $webPath = $pathInfo->getWebAccessibleTarget();

    // Ensure directory structure
    if (!file_exists("$webTarget/js")) {
      mkdir("$webTarget/js", 0755, true);
    }
    if (!file_exists("$webTarget/img")) {
      mkdir("$webTarget/img", 0755, true);
    }

    // Copy any supporting files to the web target
    $workingImg = __DIR__ . "/working.gif";
    copy($workingImg, "$webTarget/img/working.gif");

    // Generate script
    $templateLoader = CodeTemplateLoader::get(__DIR__);

    $workingImgInfo = getimagesize($workingImg);
    $templateValues = array(
      'basePath'  => $webPath,
      'imgWidth'  => $workingImgInfo[0],
      'imgHeight' => $workingImgInfo[1]
    );
    $src = $templateLoader->load('conductor.js', $templateValues);
    file_put_contents("$webTarget/js/conductor.js", $src);
  }
}
