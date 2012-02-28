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

use \zeptech\orm\generator\PersisterGenerator;
use \zeptech\orm\generator\TransformerGenerator;
use \zeptech\orm\generator\ValidatorGenerator;
use \DirectoryIterator;

/**
 * This class compiles a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Compiler {

  public static function compile($pathInfo, $ns) {
    // Compile Conductor models
    $cdtModelDir = "$pathInfo[lib]/conductor/src/model";
    self::_compileModels($cdtModelDir, 'conductor\\model', $pathInfo['target']);

    // Compile Site models
    $modelDir = "$pathInfo[src]/$ns/model";
    self::_compileModels($modelDir, "$ns\\model", $pathInfo['target']);
  }

  private static function _compileModels($models, $ns, $target) {
    $persisterGen = new PersisterGenerator($target);
    $transformerGen = new TransformerGenerator($target);
    $validatorGen = new ValidatorGenerator($target);

    $dir = new DirectoryIterator($models);
    foreach ($dir as $model) {
      if ($model->isDot() || $model->isDir()) {
        continue;
      }

      $fname = $model->getFilename();
      if (substr($fname, -4) !== '.php') {
        continue;
      }

      $modelName = substr($fname, 0, -4);
      $modelClass = "$ns\\$modelName";

      $persisterGen->generate($modelClass);
      $transformerGen->generate($modelClass);
      $validatorGen->generate($modelClass);
    }
  }
}
