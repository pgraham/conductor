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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use zpt\anno\AnnotationFactory;
use zpt\anno\Annotations;
use zpt\cdt\compile\resource\ResourceCompiler;
use zpt\cdt\crud\CrudService;
use zpt\cdt\di\Injector;
use zpt\cdt\html\NotAPageDefinitionException;
use zpt\cdt\i18n\ModelDisplayParser;
use zpt\cdt\i18n\ModelMessages;
use zpt\cdt\rest\ServiceRequestDispatcher;
use zpt\dyn\Configurator;
use zpt\opal\DefaultNamingStrategy as CompanionNamingStrategy;
use zpt\orm\companion\PersisterGenerator;
use zpt\orm\companion\QueryBuilder;
use zpt\orm\companion\TransformerGenerator;
use zpt\orm\companion\ValidatorGenerator;
use zpt\orm\model\parser\DefaultNamingStrategy as ModelNamingStrategy;
use zpt\orm\model\parser\ModelParser;
use zpt\orm\model\ModelCache;
use zpt\pct\CodeTemplateParser;
use zpt\util\File;
use zpt\util\StringUtils;
use DirectoryIterator;
use Exception;
use ReflectionClass;

/**
 * This class compiles a site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SiteCompiler implements LoggerAwareInterface {

	const ENV_DEV = 'dev';
	const ENV_STAGE = 'stage';
	const ENV_PROD = 'prod';

	private $logger;

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

	/* Html Compiler */
	private $htmlCompiler;

	/* Javascript library compiler. */
	private $jslibCompiler;

	/* Localization compiler. */
	private $l10nCompiler;

	/* Path to the site's modules directory. */
	private $modulesPath;

	/* Resources compiler. */
	private $resourcesCompiler;

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

	public function setDependencyInjectionCompiler(Compiler $compiler) {
		$this->diCompiler = $compiler;
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * Compile the website found at the given root path.
	 *
	 * @param string $root The root path of the website to comile.
	 * @param ComposerAutoloader $loader The composer autoloader.
	 * @param string $env The target environment. One of the ENV_* constants of
	 *   this class.
	 */
	public function compile($root, $loader, $env = 'dev') {
		$this->logger->info("Compiling site rooted at $root for $env environment");
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
		$loader->add($ns, $pathInfo['src']);

		// Initiate the compiler
		$this->initCompiler($pathInfo, $env);

		// Add XML dependency files before any annotation configured beans get
		// added as annotation configured beans may depend on XML configured beans
		// but the converse should not be true and is not supported
		$this->collectDependencyXmls($pathInfo);

		// Ensure that the target directories are created
		if (!file_exists("$pathInfo[target]/htdocs")) {
			mkdir("$pathInfo[target]/htdocs", 0755, true);
		}

		// Compile server dispatcher
		$this->dispatcherCompiler->compile($pathInfo, $ns, $env);

		$this->compileModels($pathInfo, $ns);
		$this->compileServices($pathInfo, $ns);
		$this->resourcesCompiler->compile($pathInfo, $ns, $env);
		$this->htmlCompiler->compile($pathInfo, $ns, $env);
		if ($env !== self::ENV_DEV) {
			$this->resourcesCompiler->combineResourceGroups(
				"$pathInfo[target]/htdocs"
			);
		}

		$this->compileJslibs($pathInfo, $ns);
		$this->compileLanguageFiles($pathInfo, $ns);

		$this->diCompiler->compile($pathInfo, $ns);

		$this->serverCompiler->compile($pathInfo);
	}

	protected function collectDependencyXmls($pathInfo) {
		$diCompiler = $this->diCompiler;
		$diCompiler->addFile(
			"$pathInfo[cdtRoot]/resources/dependencies.xml"
		);

		$this->doWithModules(function ($modulePath) use ($diCompiler) {
			$diPath = "$modulePath/resources/dependencies.xml";
			if (file_exists($diPath)) {
				$diCompiler->addFile($diPath);
			}
		});

		$siteDiPath = "$pathInfo[src]/resources/dependencies.xml";
		if (file_exists($siteDiPath)) {
			$diCompiler->addFile($siteDiPath);
		}
	}

	protected function compileJslibs($pathInfo, $ns) {
		// TODO Once all front end dependencies have been migrated to bower this
		// will no longer be necessary
		$this->compileJslibDir($pathInfo, "$pathInfo[root]/lib/jslib");

		// Copy bower build artifact to htdocs
		$libSrc = "$pathInfo[cdtRoot]/vendor/target/build";
		$libOut = "$pathInfo[target]/htdocs/lib";

		File::copy($libSrc, $libOut);

		$this->doWithModules(function ($modulePath) use ($pathInfo) {
			if (file_exists("$modulePath/htdocs/lib")) {
				File::copy("$modulePath/htdocs/lib", "$pathInfo[target]/htdocs/lib");
			}
		});
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
		$this->compileLanguageDir("$pathInfo[cdtRoot]/resources/i18n");

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
			"$pathInfo[cdtRoot]/src/model",
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

	protected function compileServices($pathInfo, $ns) {
		// Compile Conductor services
		$this->serviceCompiler->compile(
			"$pathInfo[cdtRoot]/src/srvc",
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

	protected function compileJslibDir($pathInfo, $dir) {
		if (!file_exists($dir)) {
			return;
		}

		$jslibOut = "$pathInfo[target]/htdocs/lib";
		if (!file_exists($jslibOut)) {
			mkdir($jslibOut, 0755, true);
		}

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
		if ($this->logger === null) {
			$this->logger = new NullLogger;
		}

		if ($this->configurationCompiler === null) {
			$this->configurationCompiler = new ConfigurationCompiler();
		}

		if ($this->dispatcherCompiler === null) {
			$this->dispatcherCompiler = new DispatcherCompiler();
		}

		if ($this->diCompiler === null) {
			$this->diCompiler = new DependencyInjectionCompiler();
		}

		if ($this->resourcesCompiler === null) {
			$this->resourcesCompiler = new ResourcesCompiler();
		}

		if ($this->htmlCompiler === null) {
			$this->htmlCompiler = new HtmlCompiler(
				$this->diCompiler,
				$this->resourcesCompiler,
				$this->serverCompiler
			);
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

		$this->modulesPath = "$pathInfo[root]/modules";

		// Dependency Injection
		$serviceRequestDispatcher = new ServiceRequestDispatcher(
			$pathInfo['target']);
		$serviceRequestDispatcher->setLogger($this->logger);
		$this->serviceCompiler->setServiceRequestDispatcher(
			$serviceRequestDispatcher);
		$this->serviceCompiler->setDependencyInjectionCompiler($this->diCompiler);
		$this->serviceCompiler->setServerCompiler($this->serverCompiler);

		$this->diCompiler->setTemplateParser($this->tmplParser);
	}
}
