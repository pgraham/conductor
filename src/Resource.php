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

  public function __construct($resource, WebSitePathInfo $pathInfo,
      array $templateValues = null)
  {
    $webTarget = $pathInfo->getWebTarget();
    $webPath = $pathInfo->getWebAccessibleTarget();

    $resourceType = $this->_determineResourceType($resource);

    if (defined('DEBUG') && DEBUG === true) {
      $resourceTarget = "$webTarget/$resourceType";
      if (!file_exists($resourceTarget)) {
        mkdir($resourceTarget, 0755, true);
      }

      if ($templateValues === null) {
        $srcPath = __DIR__ . "/resources/$resourceType/$resource";
        copy($srcPath, "$resourceTarget/$resource");
      } else {
        $templateLoader = CodeTemplateLoader::get(
          __DIR__ . "/resources/$resourceType");
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
    } else if ($this->_elm instanceof BodyItem) {
      $this->_elm->addToBody();
    }
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
