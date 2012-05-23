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

use \zpt\cdt\html\HtmlProvider;
use \conductor\modeling\ModelInfo;
use \conductor\CrudService;
use \reed\String;
use \zeptech\anno\Annotations;
use \zeptech\orm\generator\PersisterGenerator;
use \zeptech\orm\generator\TransformerGenerator;
use \zeptech\orm\generator\ValidatorGenerator;
use \zeptech\orm\QueryBuilder;
use \zpt\pct\CodeTemplateParser;
use \DirectoryIterator;
use \ReflectionClass;

/**
 * This class compiles a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Compiler {

  private $_htmlProvider;

  private $_compressed;
  private $_tmplParser;
  private $_jslibCompiler;

  // Array of mappings for the REST server configurator that gets generated at
  // the end of compilation
  private $_mappings = array();
  
  public function __construct($compressed = false) {
    $this->_compressed = $compressed;

    $this->_tmplParser = new CodeTemplateParser();
    $this->_jslibCompiler = new JslibCompiler($compressed);
  }

  public function compile($pathInfo, $ns) {
    $this->_initGenerators($pathInfo['target']);

    // Compile server dispatcher
    copy(
      "$pathInfo[lib]/conductor/src/resources/rest/.htaccess",
      "$pathInfo[target]/htdocs/.htaccess");
    copy(
      "$pathInfo[lib]/conductor/src/resources/rest/srvr.php",
      "$pathInfo[target]/htdocs/srvr.php");

    // Compile site
    $this->compileDiContainer($pathInfo, $ns);
    $this->compileModels($pathInfo, $ns);
    $this->compileServices($pathInfo, $ns);
    $this->compileResources($pathInfo, $ns);
    $this->compileJsLibs($pathInfo, $ns);
    $this->compileModules($pathInfo, $ns);
    $this->compileServerConfigurator($pathInfo, $ns);
  }

  protected function compileDiContainer($pathInfo, $ns) {
    // Compile list of application contexts to parse
    $contexts = array(
      "$pathInfo[lib]/conductor/dependencies.xml"
    );

    $beans = array();
    foreach ($contexts as $context) {
      if (file_exists($context)) {
        $cfg = simplexml_load_file($context, 'SimpleXMLElement',
          LIBXML_NOCDATA);

        $beanDefs = $cfg->bean;
        if ($beanDefs && !is_array($beanDefs)) {
          $beanDefs = array($beanDefs);
        }

        foreach ($beanDefs as $beanDef) {
          $bean = array();
          $bean['id'] = $beanDef['id'];
          $bean['class'] = $beanDef['class'];

          $props = array();
          $refs = array();
          if (isset($beanDef->property)) {
            $propDefs = $beanDef->property;
            if (!is_array($propDefs)) {
              $propDefs = array($propDefs);
            }

            foreach ($propDefs as $propDef) {
              $prop = array();
              $prop['name'] = $propDef['name'];

              if (isset($propDef['value'])) {
                $val = $propDef['value'];
                if (is_numeric($val)) {
                  $val = (float) $val;
                } else if (strtolower($val) === 'true') {
                  $val = true;
                } else if (strtolower($val) === 'false') {
                  $val = false;
                }
                $prop['val'] = $val;
                
                $props[] = $prop;
              } else if (isset($propDef['ref'])) {
                $prop['ref'] = $propDef['ref'];
                $refs[] = $prop;
              } else {
                // TODO Warn about an invalid bean definition
              }
            }
          }
          $bean['props'] = $props;
          $bean['refs'] = $refs;

          $beans[] = $bean;
        }
      }
    }

    // Build the InjectionConfiguration script
    $srcPath = "$pathInfo[lib]/conductor/src/resources/tmpl/injection.tmpl.php";
    $outPath = "$pathInfo[target]/zeptech/dynamic/injection.php";
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));
    $tmpl->save($outPath, array('beans' => $beans));

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
      $pathInfo,
      "$pathInfo[lib]/conductor/src/model",
      'conductor\\model',
      $pathInfo['target']);

    // Compile Site models
    $this->_compileModelDir(
      $pathInfo,
      "$pathInfo[src]/$ns/model",
      "$ns\\model",
      $pathInfo['target']);
  }

  protected function compileModules($pathInfo, $ns) {
    $target = "$pathInfo[target]/htdocs";
    $modDir = "$pathInfo[root]/modules";

    if (!file_exists($modDir)) {
      return;
    }

    $modules = array();
    $dir = new DirectoryIterator($modDir);
    foreach ($dir as $module) {
      if ($module->isDot() || !$module->isDir()) {
        continue;
      }

      $modDir = $module->getPathname();
      $modName = $module->getBasename();
      $modDocs = "$modDir/htdocs";

      $modules[] = $modName;

      // We need to first remove any existing module files otherwise the copy
      // command will copy the module's htdocs folder into the existing module
      // target instead of creating a copy of htdocs at the module target
      $modTarget = "$target/$modName";
      if (file_exists($modTarget)) {
        $rmCmd = "rm -r $modTarget";
        exec($rmCmd);
      }
      $cpCmd = "cp -a $modDocs $target/$modName";
      exec($cpCmd);

      // Compile module models and services
      $modSrc = $module->getPathname() . "/zpt/mod/$modName";
      $modBaseNs = "zpt\\mod\\$modName";

      $this->_compileModelDir(
        $pathInfo,
        "$modSrc/model",
        "$modBaseNs\\model",
        $pathInfo['target'],
        "/$modName");

      $this->_compileServiceDir(
        "$modSrc/srvc",
        "$modBaseNs\\srvc",
        $pathInfo['target']);
    }
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

    // Build html mappers
    $htmlDir = "$pathInfo[src]/$ns/html";
    $this->_compileHtmlDir($htmlDir, $ns);

    // Generator the Configurator
    $values = array( 'mappings' => $this->_mappings );
    $this->_compileResource($tmplSrc, $tmplOut, $values);
  }

  protected function compileServices($pathInfo, $ns) {
    // Compile Conductor models
    $this->_compileServiceDir(
      "$pathInfo[lib]/conductor/src/srvc",
      'conductor\\srvc',
      $pathInfo['target']);

    // Compile Site models
    $this->_compileServiceDir(
      "$pathInfo[src]/$ns/srvc",
      "$ns\\srvc",
      $pathInfo['target']);
  }

  private function _compileHtmlDir($htmlDir, $ns, $tmplBase = '') {
    $tmplBase = rtrim($tmplBase, '/');

    $dir = new DirectoryIterator($htmlDir);
    foreach ($dir as $pageDef) {
      $tmpls = array();
      if ($pageDef->isDot() || substr($pageDef->getFileName(), 0, 1) === '.') {
        continue;
      }

      if ($pageDef->isDir()) {
        $dirTmplBase = $tmplBase . '/' . $pageDef->getBasename();
        $this->_compileHtmlDir($pageDef->getPathname(), $ns, $dirTmplBase);
        continue;
      }

      $extension = substr($pageDef->getFilename(), -4);
      if ($extension !== '.php') {
        continue;
      }

      $pageId = $pageDef->getBasename('.php');
      if (strlen($pageId) > 8 && substr($pageId, -8) === 'Template') {
        // This file is a page template definition
        continue;
      }

      if ($tmplBase === '') {
        $viewClass = "$ns\\html\\$pageId";
      } else {
        $viewNs = str_replace('/', '\\', ltrim($tmplBase, '/'));
        $viewClass = "$ns\\html\\$viewNs\\$pageId";
      }

      $this->_htmlProvider->generate($viewClass);

      $hdlr = '\zpt\cdt\html\HtmlRequestHandler';
      $args = array( "'$viewClass'" );
      $tmpls[] = $tmplBase . '/' . String::fromCamelCase($pageId) . '.html';
      $tmpls[] = $tmplBase . '/' . String::fromCamelCase($pageId) . '.php';
      if ($pageId === 'Index') {
        if ($tmplBase === '') {
          $tmpls[] = '/';
        } else {
          $tmpls[] = $tmplBase;
        }
      }

      $mapping = array(
        'hdlr' => $hdlr,
        'hdlrArgs' => $args,
        'tmpls' => $tmpls
      );

      $this->_mappings[] = $mapping;
    }
  }

  private function _compileModelDir($pathInfo, $models, $ns, $target,
      $urlBase = '')
  {
    if (!file_exists($models)) {
      // Nothing to do here
      return;
    }

    // TODO These should be class variables
    $persisterGen = new PersisterGenerator($target);
    $transformerGen = new TransformerGenerator($target);
    $validatorGen = new ValidatorGenerator($target);
    $infoGen = new ModelInfo($target);
    $queryBuilderGen = new QueryBuilder($target);

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
      $annos = new Annotations(new ReflectionClass($modelClass));

      // TODO - Update generators to receive an injected template parser.
      $persisterGen->generate($modelClass);
      $transformerGen->generate($modelClass);
      $validatorGen->generate($modelClass);
      $infoGen->generate($modelClass);
      $queryBuilderGen->generate($modelClass);

      if ( !isset($annos['nocrud']) ) {
        // Generate a crud service for the model
        $crudGen = new CrudService($modelClass);
        $crudGen->generate($pathInfo);

        // Create a mapping for the REST server that maps to the CrudService
        $crudInfo = $crudGen->getInfo();
        $url = "$urlBase/" . strtolower($crudInfo->getDisplayNamePlural());

        $this->_mappings[] = array(
          'hdlr' => '\\conductor\\crud\\CrudRequestHandler',
          'hdlrArgs' => array(
            "'$modelClass'"
          ),
          'tmpls' => array (
            $url,
            "$url/{id}"
          )
        );
      }
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
      if (substr($resource->getFilename(), 0, 1) === '.') {
        continue;
      }
      copy($resource->getPathname(), "$outDir/" . $resource->getFilename());
    }
  }

  private function _compileServiceDir($srvcs, $ns, $target) {
    if (!file_exists($srvcs)) {
      // Nothing to do here
      return;
    }

    $dir = new DirectoryIterator($srvcs);
    foreach ($dir as $srvc) {
      if ($srvc->isDot() || $srvc->isDir()) {
        continue;
      }

      $fname = $srvc->getFilename();
      if (substr($fname, -4) !== '.php') {
        continue;
      }

      $srvcName = substr($fname, 0, -4);
      $srvcClass = "$ns\\$srvcName";

      $annos = new Annotations(new ReflectionClass($srvcClass));
      $uris = $annos['uri'];
      if (!is_array($uris)) {
        $uris = array($uris);
      }

      $this->_mappings[] = array(
        'hdlr' => "\\$srvcClass",
        'hdlrArgs' => array(),
        'tmpls' => $uris
      );
    }
  }

  private function _initGenerators($target) {
    $this->_htmlProvider = new HtmlProvider($target);
  }
}
