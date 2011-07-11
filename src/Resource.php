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

use \conductor\compile\Compilable;
use \conductor\compile\ResourceCompiler;

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
class Resource implements Compilable {

  public static $IMG_TYPES = array(
    'png',
    'gif',
    'jpg',
    'jpeg'
  );

  /**
   * Determine the file system path for a specified resource within the
   * resources directory structure.
   *
   * @param string $resource
   * @return string Absolute path to the specified resource, false if the
   *   resource does not exist.
   */
  public static function getResourcePath($resource) {
    $type = self::getResourceType($resource);

    if (strpos($resource, '/') === false) {
      // Resource is within the resources directory structure
      if ($type === null) {
        return __DIR__ . "/resources/$resource";
      } else {
        return __DIR__ . "/resources/$type/$resource";
      }
    } else if (substr($resource, 0, 1) === '/') {
      // Resource is specified as absolute
      return $resource;
    } else {
      // Resource is specified as relative to the resources directory
      return __DIR__ . "/resources/$resource";
    }
  }

  /**
   * Determine the type of the given resource.  Either js, css, img or null for
   * unsupported.
   *
   * @param string $resource
   * @return string The type of the given resource or null if unrecognized.
   */
  public static function getResourceType($resource) {
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

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  // TODO Create support code for compilables that handles this
  /*
   * Since Resources are often part of composite compilables, this ensures that
   * each instance of a resource is only compiled once when running in debug
   * mode where it is likely that the resource will be compiled explicitely by
   * the composite and implicitely when it is added to the page.
   */
  private $_compiled = false;

  private $_elm;

  private $_name;

  private $_type;

  /**
   * Construct a new resource wrapper.  If DEBUG mode is enabled, then the
   * identified resource is copied from it's source (presumably
   * non-webaccessible directory) into the web target.  If template values are
   * provided then they are substituted into the source before being output.
   *
   * @param string $resource The name of the resource.
   */
  public function __construct($resource) {
    $this->_name = $resource;
    $this->_type = self::getResourceType($resource);
  }

  /** 
   * Add the encapsulated resource to the page.  If the resource is an image it
   * will be appended to the page body, otherwise if it is a javascript or
   * stylesheet it will be added to the page head.  This function does nothing
   * for unsupported resource types.
   */
  public function addToPage() {
    $pathInfo = Conductor::$config['pathInfo'];

    if (Conductor::isDebug()) {
      $this->compile($pathInfo);
    }

    $webPath = $pathInfo->getWebAccessibleTarget();
    $name = basename($this->_name);
    if ($this->_type !== null) {
      $resourcePath = "$webPath/{$this->_type}/$name";
    } else {
      $resourcePath = "$webPath/$name";
    }

    switch ($this->_type) {
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

    if ($this->_elm === null) {
      return;
    }

    if ($this->_elm instanceof HeadItem) {
      $this->_elm->addToHead();
    } else if ($this->_elm instanceof BodyItem) {
      $this->_elm->addToBody();
    }
  }

  /**
   * Compile this resource by copying it to the site's 
   *
   * @param WebSitePathInfo $pathInfo Encapsulated path information about the
   *   web site.
   * @param array $values Symbol table for resource compilation.
   */
  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    if ($this->_compiled) {
      return;
    }
    $this->_compiled = true;

    $compiler = new ResourceCompiler($this);
    $compiler->compile($pathInfo, $values);
  }

  public function getName() {
    return $this->_name;
  }

  public function getType() {
    return $this->_type;
  }
}
