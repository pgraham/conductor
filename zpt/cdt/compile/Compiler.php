<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile;

use \zeptech\anno\AnnotationFactory;
use \zeptech\anno\Annotations;
use \zeptech\orm\generator\PersisterGenerator;
use \zeptech\orm\generator\TransformerGenerator;
use \zeptech\orm\generator\ValidatorGenerator;
use \zeptech\orm\QueryBuilder;
use \zpt\cdt\compile\resource\ResourceCompiler;
use \zpt\cdt\crud\CrudService;
use \zpt\cdt\di\Injector;
use \zpt\cdt\html\HtmlProvider;
use \zpt\cdt\html\NotAPageDefinitionException;
use \zpt\cdt\i18n\ModelDisplayParser;
use \zpt\cdt\i18n\ModelMessages;
use \zpt\cdt\rest\ServiceRequestDispatcher;
use \zpt\dyn\Configurator;
use \zpt\orm\model\parser\DefaultNamingStrategy;
use \zpt\orm\model\parser\ModelParser;
use \zpt\orm\model\ModelCache;
use \zpt\pct\CodeTemplateParser;
use \zpt\pct\DefaultActorNamingStrategy;
use \zpt\util\File;
use \zpt\util\String;
use \DirectoryIterator;
use \Exception;
use \ReflectionClass;
use \SplClassLoader;

/**
 * This class compiles a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Compiler {

  private $modelParser;
  private $modelCache;
  private $namingStrategy;
  private $annotationFactory;

  private $persisterGen;
  private $transformerGen;
  private $validatorGen;
  private $infoGen;
  private $crudGen;
  private $queryBuilderGen;

  /* Configuration compiler. */
  private $_configurationCompiler;

  /* Dependency Injection compiler. */
  private $_diCompiler;

  /* Html Provider Generator. */
  private $_htmlProvider;

  /* Javascript library compiler. */
  private $_jslibCompiler;

  /* Localization compiler. */
  private $_l10nCompiler;

  /* Path to the site's modules directory. */
  private $_modulesPath;

  /* Resource compiler. */
  private $_resourceCompiler;

  /* REST server configurator compiler. */
  private $_serverCompiler;

  /* Service compiler */
  private $_serviceCompiler;

  /* Code template parser. */
  private $_tmplParser;

  /**
   * Create a new site compiler.
   */
  public function __construct() {
    $this->annotationFactory = new AnnotationFactory();
    $this->namingStrategy = new DefaultNamingStrategy();
    $this->modelCache = new ModelCache();
    $this->modelParser = new ModelParser();

    $this->namingStrategy->setAnnotationFactory($this->annotationFactory);

    $this->modelCache->setModelParser($this->modelParser);

    $this->modelParser->setAnnotationFactory($this->annotationFactory);
    $this->modelParser->setNamingStrategy($this->namingStrategy);
    $this->modelParser->setModelCache($this->modelCache);
    $this->modelParser->init();

    $this->_tmplParser = new CodeTemplateParser();

    $this->_configurationCompiler = new ConfigurationCompiler();
    $this->_configurationCompiler->setTemplateParser($this->_tmplParser);

    $this->_diCompiler = new DependencyInjectionCompiler();
    $this->_diCompiler->setTemplateParser($this->_tmplParser);

    $this->_jslibCompiler = new JslibCompiler();

    $this->_l10nCompiler = new L10NCompiler();
    $this->_l10nCompiler->setTemplateParser($this->_tmplParser);

    $this->_serverCompiler = new ServerCompiler();
    $this->_serverCompiler->setTemplateParser($this->_tmplParser);

    $this->_serviceCompiler = new ServiceCompiler();
    $this->_serviceCompiler->setDependencyInjectionCompiler($this->_diCompiler);
    $this->_serviceCompiler->setServerCompiler($this->_serverCompiler);

    $this->_resourceCompiler = new ResourceCompiler();
  }

  /**
   * Compile the website found at the given root path.
   *
   * @param string $root The root path of the website to comile.
   */
  public function compile($root, $env = 'dev') {
    // Configuration needs to be compiled first so that the site path
    // information is available for the rest of the compilation process
    $this->_configurationCompiler->compile($root, $env);

    // The rest of the compilation process needs the environment configuration.
    // This will also register the global functions for working with context
    // sensitive paths
    $config = Configurator::getConfig();
    $pathInfo = $config['pathInfo'];
    $ns = $config['namespace'];

    // The rest of the compilation process will require the site's namespace
    // to be registered so that class files can be reflected.
    $ldr = new SplClassLoader($ns, $pathInfo['src']);
    $ldr->register();

    // Initiate the compiler
    $this->_initCompiler($pathInfo, $env);

    // Compile server dispatcher
    copy(
      "$pathInfo[lib]/conductor/htdocs/.htaccess",
      "$pathInfo[target]/htdocs/.htaccess");
    copy(
      "$pathInfo[lib]/conductor/htdocs/srvr.php",
      "$pathInfo[target]/htdocs/srvr.php");

    $this->compileModels($pathInfo, $ns);
    $this->compileServices($pathInfo, $ns);
    $this->compileResources($pathInfo, $ns);
    $this->compileJslibs($pathInfo, $ns);
    $this->compileModules($pathInfo, $ns);
    $this->compileLanguageFiles($pathInfo, $ns);
    $this->compileHtml($pathInfo, $ns);

    $this->collectDependencyXmls($pathInfo);
    $this->_diCompiler->compile($pathInfo, $ns);

    $this->_serverCompiler->compile($pathInfo);
  }

  protected function collectDependencyXmls($pathInfo) {
    $diCompiler = $this->_diCompiler;
    $diCompiler->addFile(
      "$pathInfo[lib]/conductor/resources/dependencies.xml");

    $this->_doWithModules(function ($modulePath) use ($diCompiler) {
      $diCompiler->addFile("$modulePath/resources/dependencies.xml");
    });

    $diCompiler->addFile("$pathInfo[src]/resources/dependencies.xml");
  }

  protected function compileJslibs($pathInfo, $ns) {
    $this->_compileJslibDir($pathInfo, "$pathInfo[lib]/jslib");
    $this->_compileJslibDir($pathInfo, "$pathInfo[lib]/conductor/lib/jslib");
  }

  /**
   * Compile The site's language files.
   * ----------------------------------
   * 
   * Language file compilation involves parsing properties files and building
   * a hash or string for each language and outputting a php script which can
   * be parsed quicker than parsing the language properties files at runtime.
   *
   * Language files are defined by conductor, modules and by the site.
   * Compiled language files, in order, are:
   *
   *    * lib/conductor/src/resources/i18n/<lang>.messages
   *    * modules/<mod-name/resources/i18n/<lang>.messages
   *    * src/resources/i18n/<lang>.messages
   *
   * Any string defined by conductor can be overridden by a module or by the
   * site and any string defined by a module can be overriden by the site by
   * defining a string with the same key in the site's (or module's) language
   * file.
   */
  protected function compileLanguageFiles($pathInfo, $ns) {
    $compiler = $this;

    // Compile conductor language files
    $this->_compileLanguageDir("$pathInfo[lib]/conductor/resources/i18n");

    // Compile module language files
    $this->_doWithModules(function ($modulePath) use ($compiler) {
      $compiler->_compileLanguageDir("$modulePath/resources/i18n");
    });

    // Compile site language files
    $this->_compileLanguageDir("$pathInfo[src]/resources/i18n");

    $this->_l10nCompiler->compile($pathInfo);
    
  }

  protected function compileModels($pathInfo, $ns) {
    // Compile Conductor models
    $this->_compileModelDir(
      $pathInfo,
      "$pathInfo[lib]/conductor/zpt/cdt/model",
      'zpt\\cdt\\model');

    // Compile Site models
    $this->_compileModelDir(
      $pathInfo,
      "$pathInfo[src]/$ns/model",
      "$ns\\model");

    // Compile Module models
    $compiler = $this;
    $this->_doWithModules(function ($modulePath) use ($compiler, $pathInfo) {
      $modName = basename($modulePath);
      $modBaseNs = "zpt\\mod\\$modName";
      $compiler->_compileModelDir(
        $pathInfo,
        "$modulePath/zpt/mod/$modName/model",
        "$modBaseNs\\model",
        "/$modName"
      );
    });
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
      if (file_exists($modDocs)) {
        $cpCmd = "cp -a $modDocs $target/$modName";
        exec($cpCmd);
      }
    }
  }

  protected function compileResources($pathInfo, $ns) {
    $resourceOut = "$pathInfo[target]/htdocs";

    // Compile conductor resources
    // ---------------------------
    $resourceSrc = "$pathInfo[lib]/conductor/htdocs";

    // Compile base javascript
    $this->_compileResource(
      "$pathInfo[lib]/conductor/resources/base.tmpl.js",
      "$resourceOut/js/base.js",
      array(
        'rootPath' => $pathInfo['webRoot'],
        'jsns' => $ns
      ));

    // Compile javascript resources
    $this->_resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
    $this->_resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
    $this->_resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");

    // Compile site resources
    // ----------------------
    $resourceSrc = "$pathInfo[src]/resources";
    $this->_resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
    $this->_resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
    $this->_resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");
  }

  protected function compileHtml($pathInfo, $ns) {
    // Build html mappers
    $htmlDir = "$pathInfo[src]/$ns/html";
    $this->_compileHtmlDir($htmlDir, $ns);
  }

  protected function compileServices($pathInfo, $ns) {
    // Compile Conductor services
    $this->_serviceCompiler->compile(
      "$pathInfo[lib]/conductor/zpt/cdt/srvc",
      'zpt\\cdt\\srvc'
    );

    // Compile modules services
    $compiler = $this->_serviceCompiler;
    $this->_doWithModules(function ($modulePath) use ($compiler) {
      $modName = basename($modulePath);
      $modBaseNs = "zpt\\mod\\$modName";
      $compiler->compile(
        "$modulePath/zpt/mod/$modName/srvc",
        "$modBaseNs\\srvc"
      );
    });

    // Compile Site services
    $this->_serviceCompiler->compile(
      "$pathInfo[src]/$ns/srvc",
      "$ns\\srvc"
    );
  }

  private function _compileHtmlDir($htmlDir, $ns, $tmplBase = '') {
    $tmplBase = rtrim($tmplBase, '/');

    $dir = new DirectoryIterator($htmlDir);
    foreach ($dir as $pageDef) {
      $fname = $pageDef->getBasename();
      if ($pageDef->isDot() || substr($fname, 0, 1) === '.') {
        continue;
      }

      if ($pageDef->isDir()) {
        $dirTmplBase = $tmplBase . '/' . $fname;
        $this->_compileHtmlDir($pageDef->getPathname(), $ns, $dirTmplBase);
        continue;
      }

      if (!File::checkExtension($fname, 'php')) {
        continue;
      }

      $pageId = $pageDef->getBasename('.php');

      $viewClass = $pageId;
      $beanId = lcfirst($pageId);
      if ($tmplBase !== '') {
        $viewNs = str_replace('/', '\\', ltrim($tmplBase, '/'));
        $viewClass = "$viewNs\\$pageId";
        $beanId = lcfirst(String::toCamelCase($viewNs, '\\', true) . $pageId);
      }
      $viewClass = "$ns\\html\\$viewClass";
      $beanId .= 'HtmlProvider';

      try {
        $this->_htmlProvider->generate($viewClass);
      } catch (NotAPageDefinitionException $e) {
        // This is likely because the file is not a page definition so just
        // continue.
        error_log($e->getMessage());
        continue;
      }

      $namingStrategy = new DefaultActorNamingStrategy();
      $instClass = HtmlProvider::$actorNamespace . "\\" .
        $namingStrategy->getActorName($viewClass);
      $this->_diCompiler->addBean($beanId, $instClass);

      $args = array( "'$beanId'" );

      $hdlr = 'zpt\cdt\html\HtmlRequestHandler';
      $tmpls = array();
      $tmpls[] = "$tmplBase/" . String::fromCamelCase($pageId) . '.html';
      $tmpls[] = "$tmplBase/" . String::fromCamelCase($pageId) . '.php';
      if ($pageId === 'Index') {
        if ($tmplBase === '') {
          $tmpls[] = '/';
        } else {
          $tmpls[] = $tmplBase;
        }
      } else {
        // Add a mapping for retrieving only page fragment
        $this->_serverCompiler->addMapping(
          'zpt\cdt\html\HtmlFragmentRequestHandler',
          $args,
          array( "$tmplBase/" . String::fromCamelCase($pageId) . '.frag' )
        );
      }

      $this->_serverCompiler->addMapping($hdlr, $args, $tmpls);
    }
  }

  protected function _compileJslibDir($pathInfo, $dir) {
    $jslibOut = "$pathInfo[target]/htdocs/jslib";
    $jslibs = new DirectoryIterator($dir);
    foreach ($jslibs as $jslib) {
      if ($jslib->isDot()) {
        continue;
      }

      if ($jslib->isDir()) {
        $this->_jslibCompiler->compile($jslib->getPathname(), $pathInfo);
      } else {
        copy($jslib->getPathname(), "$jslibOut/{$jslib->getFilename()}");
      }
    }
  }

  // TODO This should be made private once PHP 5.4 is available.  It is public
  //      for now because it is accessed from the scope of an anonymous
  //      function.
  public function _compileLanguageDir($languageDir) {
    if (!file_exists($languageDir)) {
      return;
    }

    $dir = new DirectoryIterator($languageDir);
    foreach ($dir as $f) {
      if ($f->isDot() || $f->isDir()) {
        continue;
      }

      if (substr($f->getFilename(), -9) !== '.messages') {
        continue;
      }

      $this->_l10nCompiler->addLanguageFile($f);
    }
  }

  // TODO This should be made private once PHP 5.4 is available.  It is public
  //      for now because it is accessed from the scope of an anonymous
  //      function.
  public function _compileModelDir($pathInfo, $models, $ns, $urlBase = '') {
    if (!file_exists($models)) {
      // Nothing to do here
      return;
    }

    $target = $pathInfo['target'];

    // TODO These should be class variables
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

      // Only try and parse entities.  This allows other types of related
      // classes, such as gatekeepers, to be included in the model directory.
      if (isset($annos['Entity'])) {
        $model = $this->modelParser->parse($modelClass);

        $this->persisterGen->generate($modelClass);
        $this->transformerGen->generate($modelClass);
        $this->validatorGen->generate($modelClass);
        $this->infoGen->generate($modelClass);
        $this->queryBuilderGen->generate($modelClass);

        // Add model gatekeeper as a bean
        $gatekeeper = $model->getGatekeeper();
        if ($gatekeeper) {
          // TODO Use naming strategy to generate beanId
          $gatekeeperBeanId = Injector::generateBeanId($gatekeeper);
          $this->_diCompiler->addBean($gatekeeperBeanId,
            $gatekeeper);
        }

        if ( !isset($annos['nocrud']) ) {
          $this->crudGen->generate($modelClass);

          $actorName = $model->getActor();
          $crudSrvc = "zpt\\dyn\\crud\\$actorName";
          $beanId = $actorName . "_crudService";
          $this->_diCompiler->addBean($beanId, $crudSrvc);


          $this->_serviceCompiler->compileService($crudSrvc, $beanId);
        }
      }
    }
  }

  private function _compileResource($srcPath, $outPath, $values = array()) {
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));
    $tmpl->save($outPath, $values);
  }

  private function _doWithModules($fn) {
    if (!file_exists($this->_modulesPath)) {
      return;
    }

    $modules = new DirectoryIterator($this->_modulesPath);
    foreach ($modules as $module) {
      if ($module->isDot() || !$module->isDir()) {
        continue;
      }

      $fn($module->getPathname());
    }
  }

  private function _initCompiler($pathInfo, $env) {
    $target = $pathInfo['target'];
    $this->persisterGen = new PersisterGenerator($target);
    $this->transformerGen = new TransformerGenerator($target);
    $this->validatorGen = new ValidatorGenerator($target);
    $this->infoGen = new ModelMessages($target);
    $this->crudGen = new CrudService($target);
    $this->queryBuilderGen = new QueryBuilder($target); 

    $this->persisterGen->setModelCache($this->modelCache);
    $this->transformerGen->setModelCache($this->modelCache);
    $this->validatorGen->setModelCache($this->modelCache);
    $this->infoGen->setModelCache($this->modelCache);
    $this->crudGen->setModelCache($this->modelCache);
    $this->queryBuilderGen->setModelCache($this->modelCache);

    $this->_htmlProvider = new HtmlProvider($target, $env);

    $this->_modulesPath = "$pathInfo[root]/modules";

    $serviceRequestDispatcher = new ServiceRequestDispatcher(
      $pathInfo['target']);
    $this->_serviceCompiler->setServiceRequestDispatcher(
      $serviceRequestDispatcher);
  }
}
