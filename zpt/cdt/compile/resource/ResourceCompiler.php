<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile\resource;

use \zpt\pct\CodeTemplateParser;
use \zpt\util\File;
use \DirectoryIterator;

/**
 * This class compiles a resource directory.  A resource is an image, a css
 * file or a javascript file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompiler {

  private $_lessCompiler;
  private $templateParser;

  public function __construct() {
    $this->_lessCompiler = new LessCompiler();
    $this->templateParser = new CodeTemplateParser();
  }

  public function compile($src, $target, $values = array()) {
    if (!file_exists($src)) {
      return;
    }

    if (is_dir($src)) {
      $this->compileResourceDirectory($src, $target);
    } else {
      $this->compileResourceFile($src, $target, $values);
    }
  }

  private function compileResourceDirectory($srcDir, $outDir) {
    $dir = new DirectoryIterator($srcDir);

    if (!file_exists($outDir)) {
      mkdir($outDir, 0755, true);
    }

    foreach ($dir as $resource) {
      if ($resource->isDot() || File::isHidden($resource)) {
        continue;
      }

      $fname = $resource->getFilename();
      if ($resource->isDir()) {
        $this->compile($resource->getPathname(), "$outDir/$fname");
        continue;
      }

      // PHP5.3.6: $ext = $resource->getExtension();
      $ext = pathinfo($fname, PATHINFO_EXTENSION);
      $basename = $resource->getBasename(".$ext");
      $pathname = $resource->getPathname();
      if ($ext === 'less') {
        $this->_lessCompiler->compile($pathname, "$outDir/$basename.css");
      } else {
        copy($resource->getPathname(), "$outDir/$fname");
      }
    }
  }

  private function compileResource($src, $target, $values) {
    $tmpl = $this->templateParser->parse(file_get_contents($src));
    $tmpl->save($target, $values);
  }

}
