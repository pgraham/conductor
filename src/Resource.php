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
namespace conductor;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;
use \oboe\item\Body as BodyItem;
use \oboe\item\Head as HeadItem;
use \oboe\Image;

use \reed\generator\CodeTemplateLoader;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates a process for making a conductor resource available
 * to a site.  If DEBUG mode is on the specified resource is copied from the
 * resources directory into the web target.  Resource type is determined by
 * extension and is included in the web page via the addToHead() method.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Resource {

  public static $IMG_TYPES = array(
    'png',
    'gif',
    'jpg',
    'jpeg'
  );

  private $_elm;
  private $_fsPath;

  public function __construct($resource, WebSitePathInfo $pathInfo,
      array $templateValues = null)
  {
    $webTarget = $pathInfo->getWebTarget();
    $webPath = $pathInfo->getWebAccessibleTarget();

    $resourceType = $this->_determineResourceType($resource);
    $this->_fsPath = __DIR__ . "/resources/$resourceType/$resource";

    if (defined('DEBUG') && DEBUG === true) {
      $resourceTarget = "$webTarget/$resourceType";
      if (!file_exists($resourceTarget)) {
        mkdir($resourceTarget, 0755, true);
      }

      if ($templateValues === null) {
        copy($this->_fsPath, "$resourceTarget/$resource");
      } else {
        $templateLoader = CodeTemplateLoader::get(dirname($this->_fsPath));
        $resourceContent = $templateLoader->load($resource, $templateValues);

        file_put_contents("$resourceTarget/$resource", $resourceContent);
      }
    }

    $resourcePath = "$webPath/$resourceType/$resource";
    switch ($resourceType) {
      case 'css':
      $this->_elm = new StyleSheet($resourcePath);
      break;

      case 'js':
      $this->_elm = new Javascript($resourcePath);
      break;

      case 'img':
      $this->_elm = new Image($resourcePath, 'Image Resource');
      break;

      default:
      assert("false /* Unrecognized resource type: $resourceType */");
    }
  }

  public function addToPage() {
    if ($this->_elm === null) {
      return;
    }

    if ($this->_elm instanceof HeadItem) {
      $this->_elm->addToHead();
    }

    // Images are not added to the page in this since it is likely that
    // the image resource is reference by a stylesheet, but does not get
    // included in the page an an <img/> element
  }

  public function getFsPath() {
    return $this->_fsPath;
  }

  private function _determineResourceType($resource) {
    $resourceParts = explode('.', $resource);
    $resourceType = array_pop($resourceParts);

    if ($resourceType === 'css' || $resourceType === 'js') {
      return $resourceType;
    }

    if (in_array($resourceType, self::$IMG_TYPES)) {
      return 'img';
    }

    return null;
  }
}
