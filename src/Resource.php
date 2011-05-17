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
 * Note: Conflicts are possible due to the use of basename.  Two different
 *       resources from different paths with the same basename will result
 *       in a conflict.
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

  /**
   * Construct a new resource wrapper.  If DEBUG mode is enabled, then the
   * identified resource is copied from it's source (presumably
   * non-webaccessible directory) into the web target.  If template values are
   * provided then they are substituted into the source before being output.
   *
   * @param string $resource The name of the resource.
   * @param WebSitePathInfo $pathInfo Encapsulated path information about the
   *   web site.
   * @param array $templateValues Array of substitution values if the identified
   *   resource is a template.  This only has an effect if DEBUG mode is
   *   enabled.
   */
  public function __construct($resource, WebSitePathInfo $pathInfo,
      array $templateValues = null)
  {
    $resourceName = basename($resource);
    $resourceType = $this->_determineResourceType($resource);
    $this->_fsPath = $this->_determineFsPath($resource, $resourceType);


    if (defined('DEBUG') && DEBUG === true) {
      $resourceTarget = $pathInfo->getWebTarget();
      if ($resourceType !== null) {
        $resourceTarget .= "/$resourceType";
        if (!file_exists($resourceTarget)) {
          mkdir($resourceTarget, 0755, true);
        }
      }

      if ($templateValues === null) {
        copy($this->_fsPath, "$resourceTarget/$resourceName");
      } else {
        $templateLoader = CodeTemplateLoader::get(dirname($this->_fsPath));
        $resourceContent = $templateLoader->load($resourceName,
          $templateValues);

        file_put_contents("$resourceTarget/$resourceName", $resourceContent);
      }
    }

    $webPath = $pathInfo->getWebAccessibleTarget();
    if ($resourceType !== null) {
      $resourcePath = "$webPath/$resourceType/$resourceName";
    } else {
      $resourcePath = "$webPath/$resourceName";
    }

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
      $this->_elm = null;
    }
  }

  /**
   * If the represented resource is capable of being added to the <head/>
   * element then add it.  This method does nothing for image and
   * unrecognized resource types.
   */
  public function addToHead() {
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

  private function _determineFsPath($resource, $resourceType) {
    if (strpos($resource, '/') === false) {
      // Resource is specified as either a supported type or in
      // the resource directory for unsupported types.
      if ($resourceType === null) {
        return __DIR__ . "/resources/$resource";
      } else {
        return __DIR__ . "/resources/$resourceType/$resource";
      }
    } else if (substr($resource, 0, 1) === '/') {
      // Resource is specified as absolute
      return $resource;
    } else {
      // Resource is specified as relative to the resources directory
      return __DIR__ . "/resources/$resource";
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
