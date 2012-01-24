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

/**
 * Interface for adapters to commonly available libraries which do not live
 * whithin a site's document root.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Library {

  /**
   * In order to perform linking, compilation and inclusion, a Library
   * implementation will need to know the base path of where it's source files
   * are located and the base path of where files are output, or to be output
   * in the case of linking and compilation.  For this reason all library
   * instances must be constructed with these two paths.
   *
   * @param string $sourcePath
   * @param string $outputPath
   * @param string $webOutPath Web accessible path to the given output path.
   */
  public function __construct($sourcePath, $outputPath, $webOutPath);

  /**
   * This function is responsible for making a library's files available within
   * a site's document root.
   *
   * @param array $options Library specific options.  See implementation source
   *   for available options to each library.
   */
  public function link(array $options = null);

  /**
   * This function is responsible for compressing a library's source files in
   * order to improve performance.  Compilation should happen in-place and will
   * generally only be invoked durring a dedicated step of site deployment.
   *
   * @param array $options Library specific options.  See implementation source
   *   for options available to each library.
   */
  public function compile(array $options = null);

  /**
   * This function is responsible for including the library in a document.
   *
   * @param array $options Library specific options.  See implementation source
   *   for options available to each library.
   */
  public function inc(array $options = null);

}
