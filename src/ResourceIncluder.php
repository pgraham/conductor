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

use \oboe\Element;
use \reed\File;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates code for including resources stored anywhere in the
 * file system in a web page.  The class provides two methods:
 *
 * <ul>
 *   <li>compile(ResourceSet);
 *   <li>include(ResourceSet);
 * </ul>
 *
 * The compile(...) method is used to copy the listed resources into the
 * web-accessible target directory defined in the resource set and the
 * include(...) method is used to actually include those resources into a web
 * page.  Note that it is assumed that any images defined by the resource list
 * are required by the defined stylesheets so they will be compiled but not
 * included as they are most likely included through the stylesheets.
 *
 * If the site is in debug mode, the include method wil automatically compile
 * the resource set before inclusion.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceIncluder {

  /**
   * Copy the resource set's source files to the web path defined in the set.
   *
   * @param ResourceSet $resources
   */
  public static function compile(ResourceSet $resources) {

    $srcPath = $resources->getSrcPath();
    $outPath = $resources->getWebPath();

    if (!file_exists($outPath)) {
      mkdir($outPath, 0755, true);
    }

    $all = array_merge(
      $resources->getScripts(),
      $resources->getSheets(),
      $resources->getImages());
    foreach ($all AS $file) {
      if (!is_array($file)) {
        $file = array( 'src' => $file, 'out' => $file );
      }

      // For convenience, it is possible to include resources from outside of
      // the source path by providing an alternate in the 'base' property of
      // the resource definition
      if (isset($file['base'])) {
        $fullSrcPath = File::joinPaths($file['base'], $file['src']);
      } else {
        $fullSrcPath = File::joinPaths($srcPath, $file['src']);
      }

      $fullOutPath = File::joinPaths($outPath, $file['out']);

      $outDir = dirname($fullOutPath);
      if (!file_exists($outDir)) {
        mkdir($outDir, 0755, true);
      }

      copy($fullSrcPath, $fullOutPath);
    }
  }

  /**
   * Include the files encapsulated by the given resource set in the page. If
   * the site is operating in debug mode then the files will be copied from
   * their source to the web-accessible path defined by the resource set.
   *
   * @param ResourceSet $resources The resources to include in the page.
   * @param string $outPath The output path of the resources.
   */
  public static function inc(ResourceSet $resources) {

    if (Conductor::isDevMode()) {
      self::compile($resources);
    }

    $pathInfo = Conductor::getPathInfo();
    $baseWeb = $pathInfo->fsToWeb($resources->getWebPath());

    foreach ($resources->getExternal() AS $ext) {
      $type = $ext['type'];
      $url = $ext['url'];

      switch ($type) {
        case 'js':
        Element::javascript($url)->addToPage();
        break;

        case 'css':
        Element::styleSheet($url)->addToPage();
        break;

        default:
        assert("false /* Unrecognized resource type: $type */;");

      }
    }

    foreach ($resources->getScripts() AS $script) {
      if (is_array($script) && isset($script['static']) && $script['static']) {
        continue;
      }

      if (is_array($script)) {
        $script = $script['out'];
      }

      $jsPath = File::joinPaths($baseWeb, $script);
      Element::javascript($jsPath)->addToHead();
    }

    foreach ($resources->getSheets() AS $sheet) {
      if (is_array($sheet) && isset($sheet['static']) && $sheet['static']) {
        continue;
      }

      if (is_array($sheet)) {
        $sheet = $sheet['out'];
      }

      $cssPath = File::joinPaths($baseWeb, $sheet);
      Element::styleSheet($cssPath)->addToHead();
    }
  }
}
