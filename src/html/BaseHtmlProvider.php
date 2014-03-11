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
namespace zpt\cdt\html;

use zpt\cdt\html\Page;
use zpt\cdt\LoginForm;
use zpt\oobo\Element;

/**
 * Base class for generated {@link HtmlProvider}s.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class BaseHtmlProvider
{

	/**
	 * @Injected
	 * @Collection zpt\cdt\html\PageViewListener
	 */
	private $pageViewListeners;

	public function setPageViewListeners(array $pageViewListeners) {
		$this->pageViewListeners = $pageViewListeners;
	}

	protected function onPageView() {
		foreach ($this->pageViewListeners as $pageViewListener) {
			$pageViewListener->pageView();
		}
	}

	protected function loadLogin() {
		Element::css('http://fonts.googleapis.com/css?family=Sorts+Mill+Goudy|Varela')->addToHead();
		Element::css(_P('/css/login.css'))->addToHead();

		$this->loadJQuery();
		Element::js(_P('/js/login.js'))->addToHead();

		$login = new LoginForm();
		$login->addToBody();
		Page::dump();
	}

	abstract protected function loadJQuery();
}
