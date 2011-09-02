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
use \reed\WebSitePathInfo;

/**
 * This class encapsulates code for including resources stored anywhere in the
 * file system in a web page.  The class provides two methods:
 *
 * <ul>
 *   <li>compile(ResourceSet, $outputPath);
 *   <li>include(ResourceSet, $webPath);
 * </ul>
 *
 * The compile(...) method is used to copy the listed resources into the
 * web-accessible target directory defined by the given path info object while 
 * the include(...) method is used to actually include those resources into a
 * web page.  Note that it is assumed that any images defined by the resource
 * list are required by the defined stylesheets so they will be compiled but
 * not included as they are most likely included through the stylesheets.
 *
 * Generally the compile method will be called conditionally followed by a call
 * to the include method with the same parameters passed to compile(...):
 *
 * <code>
 *   if ($debug) {
 *     ResourceIncluder::compile($resources, $pathInfo);
 *   }
 *   ResourceIncluder::include($resources, $pathInfo);
 * </code>
 *
 * TODO - Move all path information into the resource instances
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceIncluder {

  public static function compile(ResourceSet $resources, $outPath) {

    $srcPath = $resources->getSrcPath();

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

      if (isset($file['base'])) {
        $fullSrcPath = "{$file['base']}/{$file['src']}";
      } else {
        $fullSrcPath = "$srcPath/{$file['src']}";
      }

      $fullOutPath = "$outPath/{$file['out']}";

      $outDir = dirname($fullOutPath);
      if (!file_exists($outDir)) {
        mkdir($outDir, 0755, true);
      }

      copy($fullSrcPath, $fullOutPath);
    }
  }

  public static function inc(ResourceSet $resources, $baseWeb) {
    foreach ($resources->getExternal() AS $ext) {
      if ($ext['type'] === 'js') {
        $js = new Javascript($ext['url']);
        $js->addToHead();
      } else if ($ext['type'] === 'css') {
        $css = new StyleSheet($ext['url']);
        $css->addToHead();
      } else {
        throw new Exception(
          "Unrecognized external resource type: {$ext['type']}");
      }
    }

    foreach ($resources->getScripts() AS $script) {
      if (is_array($script)) {
        $script = $script['out'];
      }

      $js = Element::javascript("$baseWeb/$script")->addToHead();
    }

    foreach ($resources->getSheets() AS $sheet) {
      if (is_array($sheet)) {
        $sheet = $sheet['out'];
      }

      $css = Element::styleSheet("$baseWeb/$sheet")->addToHead();
    }
  }
}
