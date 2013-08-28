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

use \zpt\cdt\html\HtmlProvider;
use \zpt\cdt\html\NotAPageDefinitionException;
use \zpt\cdt\html\PageResourceParser;
use \zpt\opal\DefaultNamingStrategy;
use \zpt\util\File;
use \DirectoryIterator;

/**
 * This class compiles the site's HtmlProvider companions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class HtmlCompiler implements Compiler {

	private $diCompiler;
	private $resourcesCompiler;
	private $serverCompiler;

	private $htmlProvider;

	public function __construct(
		DependencyInjectionCompiler $diCompiler,
		ResourcesCompiler $resourcesCompiler,
		ServerCompiler $serverCompiler
	) {
		$this->diCompiler = $diCompiler;
		$this->resourcesCompiler = $resourcesCompiler;
		$this->serverCompiler = $serverCompiler;
	}

	public function compile($pathInfo, $ns, $env = 'dev') {
		$this->htmlProvider = new HtmlProvider($pathInfo['target'], $env);

		$htmlDir = "$pathInfo[src]/$ns/html";
		$this->compileHtmlDir($htmlDir, $ns, $env);
	}

	private function compileHtmlDir($dir, $ns, $env, $webBase = '') {
		if (!file_exists($dir)) {
			return;
		}

		$webBase = rtrim($webBase, '/');

		$dir = new DirectoryIterator($dir);
		foreach ($dir as $pageDef) {
			$fname = $pageDef->getBasename();
			if ($pageDef->isDot() || substr($fname, 0, 1) === '.') {
				continue;
			}

			if ($pageDef->isDir()) {
				$dirTmplBase = $webBase . '/' . $fname;
				$this->compileHtmlDir($pageDef->getPathname(), $ns, $env, $dirTmplBase);
				continue;
			}

			if (!File::checkExtension($fname, 'php')) {
				continue;
			}

			$pageId = String($pageDef->getBasename('.php'));

			$viewClass = $pageId;
			$beanId = lcfirst($pageId);
			if ($webBase !== '') {
				$viewNs = str_replace('/', '\\', ltrim($webBase, '/'));
				$viewClass = "$viewNs\\$pageId";
				$beanId = lcfirst(String($viewNs)->toCamelCase('\\', true) . $pageId);
			}
			$viewClass = "$ns\\html\\$viewClass";
			$beanId .= 'HtmlProvider';

			try {
				$this->htmlProvider->generate($viewClass);
			} catch (NotAPageDefinitionException $e) {
				// Just ignore files that are not page definitions
				error_log($e->getMessage());
				continue;
			}

			$pageResourceParser = new PageResourceParser($viewClass);
			$cssGroups = $pageResourceParser->getCssGroups();
			$this->resourcesCompiler->addResourceGroups('css', $cssGroups);

			$namingStrategy = new DefaultNamingStrategy();
			$instClass = HtmlProvider::COMPANION_NAMESPACE . '\\' .
				$namingStrategy->getCompanionClassName($viewClass);
			$this->diCompiler->addBean($beanId, $instClass);

			$args = array("'$beanId'");

			$hdlr = 'zpt\cdt\html\HtmlRequestHandler';
			$tmpls = array();
			$tmpls[] = "$webBase/" . $pageId->fromCamelCase() . '.html';
			$tmpls[] = "$webBase/" . $pageId->fromCamelCase() . '.php';
			if ((string) $pageId === 'Index') {
				if ($webBase === '') {
					$tmpls[] = '/';
				} else {
					$tmpls[] = $webBase;
				}
			} else {
				// Add a mapping for retrieving only page fragment
				$this->serverCompiler->addMapping(
					'zpt\cdt\html\HtmlFragmentRequestHandler',
					$args,
					array( "$webBase/" . $pageId->fromCamelCase() . '.frag')
				);
			}

			$this->serverCompiler->addMapping($hdlr, $args, $tmpls);
		}
	}
}
