<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile;

use \zpt\util\File;
use \DirectoryIterator;

/**
 * This class compiles a resource directory.  A resource is an image, a css
 * file or a javascript file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompiler {

  public function compile($srcDir, $outDir) {
    if (!file_exists($srcDir)) {
      return;
    }
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
      copy($resource->getPathname(), "$outDir/$fname");
    }
  }
}
