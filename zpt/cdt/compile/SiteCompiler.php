<?php
/**
 * =============================================================================
 * Copyright (c) 2012, Philip Graham
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
namespace zpt\cdt\compile;

use \zpt\anno\AnnotationFactory;
use \zpt\anno\Annotations;
use \zpt\cdt\compile\resource\ResourceCompiler;
use \zpt\cdt\crud\CrudService;
use \zpt\cdt\di\Injector;
use \zpt\cdt\html\HtmlProvider;
use \zpt\cdt\html\NotAPageDefinitionException;
use \zpt\cdt\i18n\ModelDisplayParser;
use \zpt\cdt\i18n\ModelMessages;
use \zpt\cdt\rest\ServiceRequestDispatcher;
use \zpt\dyn\Configurator;
use \zpt\opal\DefaultNamingStrategy as CompanionNamingStrategy;
use \zpt\orm\actor\QueryBuilder;
use \zpt\orm\companion\PersisterGenerator;
use \zpt\orm\companion\TransformerGenerator;
use \zpt\orm\companion\ValidatorGenerator;
use \zpt\orm\model\parser\DefaultNamingStrategy as ModelNamingStrategy;
use \zpt\orm\model\parser\ModelParser;
use \zpt\orm\model\ModelCache;
use \zpt\pct\CodeTemplateParser;
use \zpt\util\File;
use \zpt\util\StringUtils;
use \DirectoryIterator;
use \Exception;
use \ReflectionClass;
use \SplClassLoader;

/**
 * This class compiles a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SiteCompiler {

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
	private $configurationCompiler;

	/* Dependency Injection compiler. */
	private $diCompiler;

	/* Dispatcher compiler */
	private $dispatcherCompiler;

	/* Html Provider Generator. */
	private $htmlProvider;

	/* Javascript library compiler. */
	private $jslibCompiler;

	/* Localization compiler. */
	private $l10nCompiler;

	/* Path to the site's modules directory. */
	private $modulesPath;

	/* Resource compiler. */
	private $resourceCompiler;

	/* REST server configurator compiler. */
	private $serverCompiler;

	/* Service compiler */
	private $serviceCompiler;

	/* Code template parser. */
	private $tmplParser;

	/**
	 * Create a new site compiler.
	 */
	public function __construct() {
		$this->annotationFactory = new AnnotationFactory();
		$this->namingStrategy = new ModelNamingStrategy();
		$this->modelCache = new ModelCache();
		$this->modelParser = new ModelParser();

		$this->namingStrategy->setAnnotationFactory($this->annotationFactory);

		$this->modelCache->setModelParser($this->modelParser);

		$this->modelParser->setAnnotationFactory($this->annotationFactory);
		$this->modelParser->setNamingStrategy($this->namingStrategy);
		$this->modelParser->setModelCache($this->modelCache);
		$this->modelParser->init();

		$this->tmplParser = new CodeTemplateParser();

		$this->jslibCompiler = new JslibCompiler();

		$this->l10nCompiler = new L10NCompiler();
		$this->l10nCompiler->setTemplateParser($this->tmplParser);

		$this->serverCompiler = new ServerCompiler();
		$this->serverCompiler->setTemplateParser($this->tmplParser);

		$this->serviceCompiler = new ServiceCompiler();
	}

	public function setConfigurationCompiler(ConfigurationCompiler $compiler) {
		$this->configurationCompiler = $compiler;
	}

	public function setDispatcherCompiler(Compiler $compiler) {
		$this->dispatcherCompiler = $compiler;
	}

	public function setResourceCompiler(ResourceCompiler $compiler) {
		$this->resourceCompiler = $compiler;
	}

	public function setDependencyInjectionCompiler(Compiler $compiler) {
		$this->diCompiler = $compiler;
	}

	/**
	 * Compile the website found at the given root path.
	 *
	 * @param string $root The root path of the website to comile.
	 */
	public function compile($root, $env = 'dev') {
		$this->ensureDependencies();

		// Configuration needs to be compiled first so that the site path
		// information is available for the rest of the compilation process
		$this->configurationCompiler->compile($root, $env);

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
		$this->initCompiler($pathInfo, $env);

		// Compile server dispatcher
		$this->dispatcherCompiler->compile($pathInfo, $ns, $env);

		$this->compileModels($pathInfo, $ns);
		$this->compileServices($pathInfo, $ns);
		$this->compileResources($pathInfo, $ns);
		$this->compileJslibs($pathInfo, $ns);
		$this->compileLanguageFiles($pathInfo, $ns);
		$this->compileHtml($pathInfo, $ns);

		$this->collectDependencyXmls($pathInfo);
		$this->diCompiler->compile($pathInfo, $ns);

		$this->serverCompiler->compile($pathInfo);
	}

	protected function collectDependencyXmls($pathInfo) {
		$diCompiler = $this->diCompiler;
		$diCompiler->addFile(
			"$pathInfo[cdtRoot]/resources/dependencies.xml");

		$this->doWithModules(function ($modulePath) use ($diCompiler) {
			$diCompiler->addFile("$modulePath/resources/dependencies.xml");
		});

		$diCompiler->addFile("$pathInfo[src]/resources/dependencies.xml");
	}

	protected function compileJslibs($pathInfo, $ns) {
		$this->compileJslibDir($pathInfo, "$pathInfo[lib]/jslib");
		$this->compileJslibDir($pathInfo, "$pathInfo[lib]/conductor/lib/jslib");
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
	 *		* lib/conductor/src/resources/i18n/<lang>.messages
	 *		* modules/<mod-name/resources/i18n/<lang>.messages
	 *		* src/resources/i18n/<lang>.messages
	 *
	 * Any string defined by conductor can be overridden by a module or by the
	 * site and any string defined by a module can be overriden by the site by
	 * defining a string with the same key in the site's (or module's) language
	 * file.
	 */
	protected function compileLanguageFiles($pathInfo, $ns) {
		$compiler = $this;

		// Compile conductor language files
		$this->compileLanguageDir("$pathInfo[lib]/conductor/resources/i18n");

		// Compile module language files
		$this->doWithModules(function ($modulePath) use ($compiler) {
			$compiler->compileLanguageDir("$modulePath/resources/i18n");
		});

		// Compile site language files
		$this->compileLanguageDir("$pathInfo[src]/resources/i18n");

		$this->l10nCompiler->compile($pathInfo);
		
	}

	protected function compileModels($pathInfo, $ns) {
		// Compile Conductor models
		$this->compileModelDir(
			$pathInfo,
			"$pathInfo[lib]/conductor/zpt/cdt/model",
			'zpt\\cdt\\model');

		// Compile Site models
		$this->compileModelDir(
			$pathInfo,
			"$pathInfo[src]/$ns/model",
			"$ns\\model");

		// Compile Module models
		$compiler = $this;
		$this->doWithModules(function ($modulePath) use ($compiler, $pathInfo) {
			$modName = basename($modulePath);
			$modBaseNs = "zpt\\mod\\$modName";
			$compiler->compileModelDir(
				$pathInfo,
				"$modulePath/zpt/mod/$modName/model",
				"$modBaseNs\\model",
				"/$modName"
			);
		});
	}

	protected function compileResources($pathInfo, $ns) {
		$resourceOut = "$pathInfo[target]/htdocs";

		// Compile conductor resources
		// ---------------------------
		$resourceSrc = "$pathInfo[cdtRoot]/htdocs";

		// Compile base javascript
		$this->resourceCompiler->compile(
			"$pathInfo[cdtRoot]/resources/base.tmpl.js",
			"$resourceOut/js/base.js",
			array(
				'rootPath' => $pathInfo['webRoot'],
				'jsns' => $ns
			));

		// -------------------------------------------------------------------------
		// ORDER HERE IS SIGNIFICANT
		// -------------------------
		// All resources of the same type (js, css, img) are compiled into the
		// same so location.	The conductor resources are compiled first followed by
		// the modules resources with the site resources compiled last.
		// This allows modules to override conductor resources and the site's
		// resources to override both conductor and module resources.
		//
		// By convention, all conductor resources are placed within a directory
		// named cdt.  Modules place their resources in a directory specific for 
		// that modules and sites place resource in a directory named the same as 
		// the site nickname.
		//
		// WARNING: When multiple modules declare the same file the result is
		//          non-deterministic
		// -------------------------------------------------------------------------
		$this->resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
		$this->resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
		$this->resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");

		$self = $this;
		$this->doWithModules(function ($moduleSrc) use ($self, $resourceOut) {
			$resourceSrc = "$moduleSrc/htdocs";
			$self->resourceCompile("$resourceSrc/js", "$resourceOut/js");
			$self->resourceCompile("$resourceSrc/css", "$resourceOut/css");
			$self->resourceCompile("$resourceSrc/img", "$resourceOut/img");
		});

		// Compile site resources
		// ----------------------
		$resourceSrc = "$pathInfo[src]/resources";
		$this->resourceCompiler->compile("$resourceSrc/js", "$resourceOut/js");
		$this->resourceCompiler->compile("$resourceSrc/css", "$resourceOut/css");
		$this->resourceCompiler->compile("$resourceSrc/img", "$resourceOut/img");
	}

	protected function compileHtml($pathInfo, $ns) {
		// Build html mappers
		$htmlDir = "$pathInfo[src]/$ns/html";
		$this->compileHtmlDir($htmlDir, $ns);
	}

	protected function compileServices($pathInfo, $ns) {
		// Compile Conductor services
		$this->serviceCompiler->compile(
			"$pathInfo[lib]/conductor/zpt/cdt/srvc",
			'zpt\\cdt\\srvc'
		);

		// Compile modules services
		$compiler = $this->serviceCompiler;
		$this->doWithModules(function ($modulePath) use ($compiler) {
			$modName = basename($modulePath);
			$modBaseNs = "zpt\\mod\\$modName";
			$compiler->compile(
				"$modulePath/zpt/mod/$modName/srvc",
				"$modBaseNs\\srvc"
			);
		});

		// Compile Site services
		$this->serviceCompiler->compile(
			"$pathInfo[src]/$ns/srvc",
			"$ns\\srvc"
		);
	}

	private function compileHtmlDir($htmlDir, $ns, $tmplBase = '') {
		if (!file_exists($htmlDir)) {
			return;
		}

		$tmplBase = rtrim($tmplBase, '/');

		$dir = new DirectoryIterator($htmlDir);
		foreach ($dir as $pageDef) {
			$fname = $pageDef->getBasename();
			if ($pageDef->isDot() || substr($fname, 0, 1) === '.') {
				continue;
			}

			if ($pageDef->isDir()) {
				$dirTmplBase = $tmplBase . '/' . $fname;
				$this->compileHtmlDir($pageDef->getPathname(), $ns, $dirTmplBase);
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
				$beanId = lcfirst(StringUtils::toCamelCase($viewNs, '\\', true) . $pageId);
			}
			$viewClass = "$ns\\html\\$viewClass";
			$beanId .= 'HtmlProvider';

			try {
				$this->htmlProvider->generate($viewClass);
			} catch (NotAPageDefinitionException $e) {
				// This is likely because the file is not a page definition so just
				// continue.
				error_log($e->getMessage());
				continue;
			}

			$namingStrategy = new CompanionNamingStrategy();
			$instClass = HtmlProvider::$actorNamespace . "\\" .
				$namingStrategy->getClassName($viewClass);
			$this->diCompiler->addBean($beanId, $instClass);

			$args = array( "'$beanId'" );

			$hdlr = 'zpt\cdt\html\HtmlRequestHandler';
			$tmpls = array();
			$tmpls[] = "$tmplBase/" . StringUtils::fromCamelCase($pageId) . '.html';
			$tmpls[] = "$tmplBase/" . StringUtils::fromCamelCase($pageId) . '.php';
			if ($pageId === 'Index') {
				if ($tmplBase === '') {
					$tmpls[] = '/';
				} else {
					$tmpls[] = $tmplBase;
				}
			} else {
				// Add a mapping for retrieving only page fragment
				$this->serverCompiler->addMapping(
					'zpt\cdt\html\HtmlFragmentRequestHandler',
					$args,
					array( "$tmplBase/" . StringUtils::fromCamelCase($pageId) . '.frag' )
				);
			}

			$this->serverCompiler->addMapping($hdlr, $args, $tmpls);
		}
	}

	protected function compileJslibDir($pathInfo, $dir) {
		if (!file_exists($dir)) {
			return;
		}

		$jslibOut = "$pathInfo[target]/htdocs/jslib";
		$jslibs = new DirectoryIterator($dir);
		foreach ($jslibs as $jslib) {
			if ($jslib->isDot()) {
				continue;
			}

			if ($jslib->isDir()) {
				$this->jslibCompiler->compile($jslib->getPathname(), $pathInfo);
			} else {
				copy($jslib->getPathname(), "$jslibOut/{$jslib->getFilename()}");
			}
		}
	}

	// TODO This should be made private once PHP 5.4 is available.	It is public
	//			for now because it is accessed from the scope of an anonymous
	//			function.
	public function compileLanguageDir($languageDir) {
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

			$this->l10nCompiler->addLanguageFile($f);
		}
	}

	// TODO This should be made private once PHP 5.4 is available.	It is public
	//			for now because it is accessed from the scope of an anonymous
	//			function.
	public function compileModelDir($pathInfo, $models, $ns, $urlBase = '') {
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
					$this->diCompiler->addBean($gatekeeperBeanId,
						$gatekeeper);
				}

				if ( !isset($annos['nocrud']) ) {
					$this->crudGen->generate($modelClass);

					$actorName = $model->getActor();
					$crudSrvc = "zpt\\dyn\\crud\\$actorName";
					$beanId = Injector::generateBeanId($crudSrvc, 'Crud');
					$this->diCompiler->addBean($beanId, $crudSrvc);


					$this->serviceCompiler->compileService($crudSrvc, $beanId);
				}
			}
		}
	}

	private function doWithModules($fn) {
		if (!file_exists($this->modulesPath)) {
			return;
		}

		$modules = new DirectoryIterator($this->modulesPath);
		foreach ($modules as $module) {
			if ($module->isDot() || !$module->isDir()) {
				continue;
			}

			$fn($module->getPathname());
		}
	}

	/* 
	 * Ensure that all injectable dependencies that are not set have a default
	 * instantiated.
	 */
	private function ensureDependencies() {
		if ($this->configurationCompiler === null) {
			$this->configurationCompiler = new ConfigurationCompiler();
		}

		if ($this->dispatcherCompiler === null) {
			$this->dispatcherCompiler = new DispatcherCompiler();
		}

		if ($this->resourceCompiler === null) {
			$this->resourceCompiler = new ResourceCompiler();
		}

		if ($this->diCompiler === null) {
			$this->diCompiler = new DependencyInjectionCompiler();
		}
	}

	/* Initialize the compiler once configuration has been parsed */
	private function initCompiler($pathInfo, $env) {
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

		$this->htmlProvider = new HtmlProvider($target, $env);

		$this->modulesPath = "$pathInfo[root]/modules";

		// Dependency Injection
		$serviceRequestDispatcher = new ServiceRequestDispatcher(
			$pathInfo['target']);
		$this->serviceCompiler->setServiceRequestDispatcher(
			$serviceRequestDispatcher);
		$this->serviceCompiler->setDependencyInjectionCompiler($this->diCompiler);
		$this->serviceCompiler->setServerCompiler($this->serverCompiler);

		$this->diCompiler->setTemplateParser($this->tmplParser);
	}

	// Kludge until anonymous function can access $this private methods
	public function resourceCompile($resourceSrc, $resourceOut) {
		$this->resourceCompiler->compile($resourceSrc, $resourceOut);
	}
}
