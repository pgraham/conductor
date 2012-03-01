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

use \pct\CodeTemplateParser;
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

  private $_tmplParser;
  
  public function __construct() {
    $this->_tmplParser = new CodeTemplateParser();
  }

  public function compile($pathInfo, $ns) {
    // Compile Conductor models
    $cdtModelDir = "$pathInfo[lib]/conductor/src/model";
    $this->_compileModels($cdtModelDir, 'conductor\\model', $pathInfo['target']);

    // Compile Site models
    $modelDir = "$pathInfo[src]/$ns/model";
    $this->_compileModels($modelDir, "$ns\\model", $pathInfo['target']);

    // Compile base javascript
    $this->_compileResource(
      __DIR__ . '/resources/js/base.tmpl.js',
      "$pathInfo[target]/htdocs/js/base.js",
      array(
        'rootPath' => $pathInfo['webRoot'],
        'jsns' => $ns
      ));
  }

  private function _compileModels($models, $ns, $target) {
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

  private function _compileResource($srcPath, $outPath, $values = null) {
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));
    $tmpl->save($outPath, $values);
  }
}
