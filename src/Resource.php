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
use \conductor\Conductor;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;
use \oboe\item\Body as BodyItem;
use \oboe\item\Head as HeadItem;
use \oboe\Element;
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
 * TODO Encapsulate distinction between an imported resource and a local
 *      resource.  Imported resources can be any file necessary for the
 *      functionality of the website that live outside of the document root.
 *      Local resources are files that live inside the document root and can
 *      be included in the page as external resources.  These include and are
 *      currently limitted to javascripts and stylesheets.
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

  /**
   * Load the specified resource.  Relative paths are treated as imported
   * resources relative to the conductor resource directory while absolute paths
   * are treated as local resources relative to the document root
   * (i.e., Absolute paths are treated as web paths relative to the web root.)
   *
   * Imported resources:
   * -------------------
   * Imported resources will be copied into the document root if in dev mode and
   * javascripts and stylesheet will be included in document head.  While not
   * in dev mode any imported resources that are not javascripts or stylesheets
   * will be ignored as they should already be present in the document root
   * due to a compile.
   *
   * Local resources:
   * ----------------
   * Local resources that are javascripts and stylesheets will be automatically
   * included in the document head.  Other resource types are ignored but no
   * error is presented.  This is to allow for cases where the path of a
   * resource (e.g. an image) could be either local or imported as would be the
   * case when a page or component will allow an imported resource to be
   * overridden by the presence of an equivalent local resource.
   *
   * NOTE: To include resources that are neither local or in the conductor
   *       resources directory use the Resource::import(...) static method with
   *       an absolute path.  This will compile the resource into the document
   *       root so that it can then be loaded as a local resource using this
   *       method.
   *
   * NOTE:  To compile imported resources in the conductor directory without
   *        including them in the document head, use the Resource::import(...)
   *        static method with a relative path.
   *
   * TODO: Write the Resource::import(...) method.
   *
   * @param string $path The path to the resource.
   */
  public static function load($path) {
    if (substr($path, 0, 1) === '/') {
      $pathInfo = Conductor::getPathInfo();
      $path = $pathInfo->webPath($path);

      switch (self::getResourceType($path)) {
        case 'js':
        Element::js($path)->addToHead();
        break;

        case 'css':
        Element::css($path)->addToHead();
      }
    } else {
      self::import($path)->addToPage();
    }
  }

  /**
   * Import a resource into the document root.  A single file name with no path
   * component will have its type auto-detected and be treated as living in the
   * appropriate directory inside the conductor resource directory.  A  relative
   * path will be treated as relative to the conductor resource directory and
   * an absolute path will be treated as an absolute file system path.
   *
   * @param string $path
   * @return Resource
   */
  public static function import($path) {
    $resource = new Resource($path);

    if (Conductor::isDebug()) {
      $resource->compile();
    }
    return $resource;
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
    $pathInfo = Conductor::getPathInfo();

    if (Conductor::isDebug()) {
      $this->compile();
    }

    $webPath = $pathInfo->fsToWeb($pathInfo->getWebTarget());
    $name = basename($this->_name);
    if ($this->_type !== null) {
      $resourcePath = "$webPath/{$this->_type}/$name";
    } else {
      $resourcePath = "$webPath/$name";
    }

    switch ($this->_type) {
      case 'css':
      $elm = new StyleSheet($resourcePath);
      $elm->addToHead();
      break;

      case 'js':
      $elm = new Javascript($resourcePath);
      $elm->addToHead();
      break;

      case 'img':
      $elm = new Image($resourcePath, 'Image Resource');
      $elm->addToBody();
      break;

      default:
      return;
    }
  }

  /**
   * Compile this resource by copying it to the site's 
   *
   * @param WebSitePathInfo $pathInfo Encapsulated path information about the
   *   web site.
   * @param array $values Symbol table for resource compilation.
   */
  public function compile(array $values = null) {
    if ($this->_compiled) {
      return;
    }
    $this->_compiled = true;

    if ($pathInfo === null)

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
