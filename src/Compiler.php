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

use \conductor\CrudService;
use \pct\CodeTemplateParser;
use \reed\String;
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

  private $_compressed;
  private $_tmplParser;
  private $_jslibCompiler;
  
  public function __construct($compressed = false) {
    $this->_compressed = $compressed;

    $this->_tmplParser = new CodeTemplateParser();
    $this->_jslibCompiler = new JslibCompiler($compressed);
  }

  public function compile($pathInfo, $ns) {
    // Compile server dispatcher
    copy(
      "$pathInfo[lib]/conductor/src/resources/rest/.htaccess",
      "$pathInfo[target]/htdocs/.htaccess");
    copy(
      "$pathInfo[lib]/conductor/src/resources/rest/srvr.php",
      "$pathInfo[target]/htdocs/srvr.php");

    // Compile site
    $this->compileModels($pathInfo, $ns);
    $this->compileServices($pathInfo, $ns);
    $this->compileResources($pathInfo, $ns);
    $this->compileJsLibs($pathInfo, $ns);
    $this->compileServerConfigurator($pathInfo, $ns);
  }

  protected function compileJsLibs($pathInfo, $ns) {
    $jslibs = new DirectoryIterator("$pathInfo[lib]/jslib");
    foreach ($jslibs as $jslib) {
      if ($jslib->isDot() || !$jslib->isDir()) {
        continue;
      }

      $jslibName = $jslib->getFilename();
      $this->_jslibCompiler->compile($jslibName, $pathInfo);
    }
  }

  protected function compileModels($pathInfo, $ns) {
    // Compile Conductor models
    $this->_compileModelDir(
      "$pathInfo[lib]/conductor/src/model",
      'conductor\\model',
      $pathInfo['target']);

    // Compile Site models
    $this->_compileModelDir(
      "$pathInfo[src]/$ns/model",
      "$ns\\model",
      $pathInfo['target']);
  }

  protected function compileResources($pathInfo, $ns) {
    $resourceOut = "$pathInfo[target]/htdocs";

    // Compile conductor resources
    // ---------------------------
    $resourceSrc = __DIR__ . '/resources';

    // Compile base javascript
    $this->_compileResource(
      "$resourceSrc/tmpl/base.tmpl.js",
      "$resourceOut/js/base.js",
      array(
        'rootPath' => $pathInfo['webRoot'],
        'jsns' => $ns
      ));

    // Compile javascript resources
    $this->_compileResourceDir("$resourceSrc/js", "$resourceOut/js");
    $this->_compileResourceDir("$resourceSrc/css", "$resourceOut/css");
    $this->_compileResourceDir("$resourceSrc/img", "$resourceOut/img");

    // Compile site resources
    // ----------------------
    $resourceSrc = "$pathInfo[src]/resources";
    $this->_compileResourceDir("$resourceSrc/js", "$resourceOut/js");
    $this->_compileResourceDir("$resourceSrc/css", "$resourceOut/css");
    $this->_compileResourceDir("$resourceSrc/img", "$resourceOut/img");
  }

  protected function compileServerConfigurator($pathInfo, $ns) {
    $tmplSrc = __DIR__ . '/resources/tmpl/ServerConfigurator.php';
    $tmplOut = "$pathInfo[target]/zeptech/dynamic/ServerConfigurator.php";

    $mappings = array();

    $htmlDir = "$pathInfo[src]/$ns/html";
    $dir = new DirectoryIterator($htmlDir);
    foreach ($dir as $pageDef) {
      if ($pageDef->isDot() || $pageDef->isDir()) {
        continue;
      }

      $extension = substr($pageDef->getFilename(), -4);
      if ($extension !== '.php') {
        continue;
      }

      $pageId = $pageDef->getBasename('.php');

      $hdlr = '\conductor\HtmlRequestHandler';
      $args = array( "'$ns\\html\\$pageId'" );
      $tmpls = array( String::fromCamelCase($pageId) . '.html' );
      if ($pageId === 'Index') {
        $tmpls[] = '/';
      }

      $mapping = array(
        'hdlr' => $hdlr,
        'hdlrArgs' => $args,
        'tmpls' => $tmpls
      );

      $mappings[] = $mapping;
    }
    $values = array( 'mappings' => $mappings );

    $this->_compileResource($tmplSrc, $tmplOut, $values);
  }

  protected function compileServices($pathInfo, $ns) {
    // TODO
  }

  private function _compileModelDir($models, $ns, $target) {
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

      // TODO - Update generators to receive an injected template parser.
      $persisterGen->generate($modelClass);
      $transformerGen->generate($modelClass);
      $validatorGen->generate($modelClass);

      // TODO - Generate CRUD service for the model
      $crudGen = new CrudService($modelClass);
      $crudGen->generate();
    }
  }

  private function _compileResource($srcPath, $outPath, $values = array()) {
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));
    $tmpl->save($outPath, $values);
  }

  private function _compileResourceDir($srcDir, $outDir) {
    if (!file_exists($srcDir)) {
      return;
    }
    $dir = new DirectoryIterator($srcDir);

    if (!file_exists($outDir)) {
      mkdir($outDir, 0755, true);
    }

    foreach ($dir as $resource) {
      if ($resource->isDot() || $resource->isDir()) {
        continue;
      }
      copy($resource->getPathname(), "$outDir/" . $resource->getFilename());
    }
  }
}
