<?php
/**
 * =============================================================================
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License. The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace /*# companionNs #*/;

use Psr\Log\LoggerAwareInterface;
use zpt\oobo\struct\FlowContent;
use zpt\oobo\Element;
use zpt\cdt\di\Injector;
use zpt\cdt\di\InjectedLoggerAwareTrait;
use zpt\cdt\html\BaseHtmlProvider;
use zpt\cdt\html\Page;
use zpt\cdt\L10N;
use zpt\cdt\LoginForm;
use zpt\cdt\LoginFormAsync;

/**
 * This is a generated class that populates a conductor\Page instance.
 *
 * DO NOT MODIFY.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class /*# companionClass #*/ extends BaseHtmlProvider
	implements LoggerAwareInterface
{

	use InjectedLoggerAwareTrait;

	/** @Injected */
	private $authProvider;

	public function populate(Page $page, array $query = null) {
		$this->logger->info("HTML: Populating HTML Page");
		#{ if env = dev
			$page->setCaptureDebug(true);
		#{ else
			$page->setCaptureDebug(false);
		#}

		#{ if title ISSET
			$page->setPageTitle('/*# title #*/');
		#}

		#{ if auth ISSET
			if (!$this->authProvider->hasPermission('/*# auth #*/', '/*# authLvl #*/')) {
				$this->logger->debug("HTML: Insufficient permissions to view page.");
				$this->loadLogin();
				exit;
			}
		#}

		#{ if template ISSET
			$tmpl = new \/*# template #*/();
			#{ if tmplDependencies ISSET
				Injector::inject($tmpl, /*# php:tmplDependencies #*/);
			#}
		#}

		// Base styles
		// -------------------------------------------------------------------------
		Element::css('/*# cssPath #*//reset.css')->addToHead();
		Element::css('/*# cssPath #*//cdt.css')->addToHead();

		#{ each coreCss as css
			Element::css('/*# cssPath #*///*# css #*/')->addToHead();
		#}
		#{ if sheets ISSET
			#{ each sheets as css
				Element::css('/*# css #*/')->addToHead();
			#}
		#}

		// Javascript libraries
		// -------------------------------------------------------------------------
		// Non-jquery
		Element::js('/*# webLib #*//date.min.js')->addToHead();
		Element::js('/*# webLib #*//moment.min.js')->addToHead();
		Element::js('/*# webLib #*//q.min.js')->addToHead();

		// JQuery - This includes jQuery and all dependent plugins
		Element::js('/*# webLib #*//jquery.all.min.js')->addToHead();
		Element::css('/*# webLib #*//jquery-ui.min.css')->addToHead();

		// -------------------------------------------------------------------------

		// Load language script
		$lang = L10N::getLang();
		Element::js("/*# jsPath #*//$lang.js")->addToHead();

		// Client support scripts
		Element::js('/*# jsPath #*//base.js')->addToHead();
		#{ each coreScripts as script
			Element::js('/*# jsPath #*///*# script #*/')->addToHead();
		#}
		#{ each utilScripts as script
			Element::js('/*# jsPath #*///*# script #*/')->addToHead();
		#}

		$this->includeJsLibs();

		#{ if jsappsupport
			Element::js('/*# webLib #*//raphael.min.js')->addToHead();
			Element::js('/*# jsPath #*//cdt/raphael-util.js')->addToHead();

			#{ each widgetCss as sheet
				Element::css('/*# cssPath #*///*# sheet #*/')->addToHead();
			#}
			Element::css('/*# cssPath #*//jsapp.css')->addToHead();

			#{ each widgetScripts as script
				Element::js('/*# jsPath #*///*# script #*/')->addToHead();
			#}
			Element::js('/*# jsPath #*//jsapp.js')->addToHead();
		#}

		#{ if fonts ISSET
			Element::css("http://fonts.googleapis.com/css?family=/*# fonts #*/")->addToHead();
		#}

		// Javascripts
		#{ if jscripts ISSET
			#{ each jscripts as jscript
				Element::js('/*# jscript #*/')->addToHead();
			#}
		#}

		#{ if template
			$page->setTemplate($tmpl);
		#}
		$page->bodyAdd($this->getContent($query));

		// Add an asynchronous login form that will be initially hidden so that it
		// can be autocompleted by the browser.
		$page->bodyAdd(new LoginFormAsync());

		// Invoke any registered page view listeners
		$this->onPageView();
	}

	public function getFragment($query) {
		#{ if auth ISSET
			if (!$this->authProvider->hasPermission('/*# auth #*/', '/*# authLvl #*/')) {
				$this->loadLogin();
				exit;
			}
		#}

		$ctnt = $this->getContent($query);

		// Invoke any registered page view listeners
		$this->onPageView();

		return $ctnt;
	}

	public function setAuthProvider($authProvider) {
		$this->authProvider = $authProvider;
	}

	private function getContent($query) {
		$ctnt = '';
		#{ if hasContent
			$ctntProvider = new \/*# contentProvider #*/();
			Injector::inject($ctntProvider, /*# php:dependencies #*/);
			$ctnt = $ctntProvider->getContent($query);
		#}

		return $ctnt;
	}

	private function includeJsLibs() {
		#{ each jslibs as jslib
			#{ if jslib = epiceditor
				Element::js('/*# jslibPath #*//epiceditor/epiceditor.js')->addToHead();
				Element::css('/*# jslibPath #*//epiceditor/epiceditor.css')->addToHead();
			#{ elseif jslib = highlight
				Element::js('/*# jslibPath #*//highlight/highlight.js')->addToHead();
				Element::css('/*# jslibPath #*//highlight/highlight.css')->addToHead();
			#{ elseif jslib = raphael
				Element::js('/*# webLib #*//raphael.min.js')->addToHead();
				Element::js('/*# jsPath #*//cdt/raphael-util.js')->addToHead();
			#{ else
				Element::js('/*# jslibPath #*///*# jslib #*/.js')->addToHead();
			#}
		#}
	}
}
