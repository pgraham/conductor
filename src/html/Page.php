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
 * @package conductor
 */
namespace conductor\html;

use \conductor\Conductor;
use \oboe\struct\FlowContent;

/**
 * This class is an extension to Oboe_Page that adds some handy features.
 * These include transparent page templating and debug capture/output.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor
 */
class Page extends \oboe\Page {

  /*
   * =========================================================================
   * Class
   * =========================================================================
   * 

  /* Whether or not we're initialized */
  private static $_initialized = false;

  /**
   * After invoking this method any output until the page is dumped will be
   * captured using output buffering and will optionally be displayed in a
   * designated area when the page is dumped.
   *
   * Once turned on, debug capturing cannot be turned off.  Calling this
   * method subsequent times will simple toggle whether or not debug captured
   * after the latest invocation will be output or not.
   *
   * @param boolean whether or not to output any captured debug, Default is
   *     to toggle.
   */
  public static function captureDebug($output = null) {
    self::getInstance()->setCaptureDebug($output);
  }

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
   * Overrides instance to be an instance of Conductor\Page.  This method gets
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

  /**
   * This method toggles page level white space suppression.  Page level white
   * space suppression can be useful for saving bandwidth in production.
   *
   * @param boolean whether or not to suppress extra white space characters
   */
  public static function suppressWhiteSpace($suppress = true) {
    self::getInstance()->setSuppressWhiteSpace($suppress);
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
   * Initializes the debug catcher.
   *
   * @param boolean whether or not to output captured debug information.
   */
  protected function setCaptureDebug($output) {
    // Don't allow debug capturing to be turned on once the page has been
    // dumped
    if ($this->_dumped) {
      return;
    }

    if ($this->_debugCatcher === null) {
      $this->_debugCatcher = new DebugCatcher();

      if ($this->_template !== null) {
        $dc = $this->_template->getDebugContainer();
        if ($db !== null) {
          $this->_debugCatcher->setOutputContainer($dc);
        }
      }
    }

    if ($output === null) {
      $output = !$this->_debugCatcher->getOutput();
    }
    $this->_debugCatcher->setOutput($output);
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
