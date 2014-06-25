<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package conductor
 */
namespace zpt\cdt\html;

/**
 * This class is an extension to Oboe_Page that adds some handy features.
 * These include transparent page templating and debug capture/output.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Page extends \zpt\oobo\Page {

	/*
	 * =========================================================================
	 * Class
	 * =========================================================================
	 * 

	/* Whether or not we're initialized */
	private static $_initialized = false;

	/**
	 * Make sure we're initialized before allowing an instance to be retieved.
	 *
	 * N.B. Since it is not possible to override static methods in PHP, any
	 * calls to Oboe\Page::getInstance() before Conductor\Page has been
	 * initialized will result in the creation an Oboe\Page instance rather than
	 * a Conductor\Page instance.
	 *
	 * @return Conductor\Page instance
	 */
	public static function getInstance() {
		self::init();
		return parent::getInstance();
	}

	/**
	 * Overrides instance to be an instance of Conductor\Page.	This method gets
	 * called when the class is first used.
	 */
	public static function init() {
		if (self::$_initialized) {
			return;
		}
		parent::setInstance(new Page());
		self::$_initialized = true;
	}

	/**
	 * Set the page's template.
	 *
	 * @param Template
	 */
	public static function setTemplate(PageTemplate $template) {
		self::getInstance()->setPageTemplate($template);
	}

	/*
	 * =========================================================================
	 * Instance
	 * =========================================================================
	 */

	/* Whether or not the page's dump method has been called */
	private $_dumped = false;

	/* If debug capturing is enabled */
	private $_debugCatcher;

	protected function __construct() {
		parent::__construct();
	}

	public function __toString() {
		if ($this->_debugCatcher !== null) {
			$this->_debugCatcher->flush();
		}
		return parent::__toString();
	}

	public function getBody() {
		return $this->_body;
	}

	public function getHead() {
		return $this->_head;
	}

	/**
	 * Initializes the debug catcher.
	 *
	 * @param boolean whether or not to output captured debug information.
	 */
	public function setCaptureDebug($output) {
		// Don't allow debug capturing to be turned on once the page has been
		// dumped
		if ($this->_dumped) {
			return;
		}

		if ($this->_debugCatcher === null) {
			$this->_debugCatcher = new DebugCatcher();
		}

		if ($output === null) {
			$output = !$this->_debugCatcher->getOutput();
		}
		$this->_debugCatcher->setOutput($output);
	}

	/**
	 * Override the dumpPage() method to handle any debug that's been captured.
	 */
	protected function dumpPage() {
		if ($this->_debugCatcher !== null) {
			$this->_debugCatcher->flush();
		}

		$this->_dumped = true;
		parent::dumpPage();
	}

	/**
	 * Set the page's template.  Once this is set, any items added to the body
	 * will be added to the div specified by the template as the area where
	 * content is placed.
	 *
	 * @param template
	 * @throws Exception
	 */
	protected function setPageTemplate(PageTemplate $template) {
		$container = $template->initialize($this);
		if ($container !== null) {
			$this->_body = $container;
		}
	}
}
