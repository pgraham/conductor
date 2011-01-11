<?php
namespace Conductor;
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
 * @package Conductor
 */
/**
 * This class is an extension to Oboe_Page that adds some handy features.
 * These include transparent page templating and debug capture/output.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package Conductor
 */
class Page extends \Oboe_Page {

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
     * calls to Oboe_Page::getInstance() before Reep_Page has been initialized
     * will result in the creation an Oboe_Page instance rather than a Reed_Page
     * instance.
     *
     * @return Reed_Page instance
     */
    public static function getInstance() {
        self::init();
        return parent::getInstance();
    }

    /**
     * Overrides instance to be an instance of Reed_Page.  This method gets
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
     * @param Reed_Template
     */
    public static function setTemplate(Template $template) {
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

    /*
     * If set, any items added to the body will actually be added to the div
     * specified by the template as the content area.
     */
    private $_template;

    /* Whether or not the page's dump method has been called */
    private $_dumped = false;

    /* If debug capturing is enabled */
    private $_debugCatcher;

    /* Object that removes extra white space characters from output */
    private $_whiteSpaceRemover;

    /* This is a singleton afterall */
    protected function __construct() {
        parent::__construct();
    }

    /**
     * Override the add to body method to add the given element to the template
     * if one is set.
     *
     * @param element
     */
    protected function bodyAdd(Oboe_Item_Body $element) {
        if ($this->_template !== null) {
            $this->_template->getContentContainer()->add($element);
        } else {
            parent::bodyAdd($element);
        }
    }

    /**
     * Override the dumpPage() method to handle any debug that's been captured.
     */
    protected function dumpPage() {
        if ($this->_debugCatcher !== null) {
            $this->_debugCatcher->flush();
        }

        if ($this->_whiteSpaceRemover !== null) {
            $this->_whiteSpaceRemover->start();
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
            $this->_debugCatcher = new Reed_DebugCatcher();

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
     * will be added to the div the specified by the template as the area where
     * content is placed.
     *
     * @param template
     * @throws Reed_Exception
     */
    protected function setPageTemplate(Template $template) {
        // Template can only be set once
        if ($this->_template !== null) {
            throw new Reed_Exception(
                'Cannot set the page template more than once');
        }

        if (\Reed_Config::isDebug()) { 
          $validator = new TemplateValidator($template);
          $validator->validate();
        }

        // If there is a debug catcher set its container to the one defined by
        // the template
        if ($this->_debugCatcher !== null) {
            $this->_debugCatcher->setOutputContainer($dc);
        }

        // Set the page's title to be the base title in case the page is
        // dumped without one
        $baseTitle = $template->getBaseTitle();
        if ($baseTitle !== null) {
            $this->_head->setTitle($baseTitle);
        }

        $this->_template = $template;
    }

    /**
     * Override the setTitle method to honour any template's base title.
     *
     * @param string title
     */
    protected function setPageTitle($title) {
        if ($this->_template !== null) {
            $baseTitle = $this->_template->getBaseTitle();
            if ($baseTitle !== null) {
                $title = $baseTitle.' - '.$title;
            }
        }
        parent::setPageTitle($title);
    }

    /**
     * Set whether or not to suppress white space.
     *
     * @param boolean
     */
    protected function setSuppressWhiteSpace($suppress) {
        if ($suppress) {
            if ($this->_whiteSpaceRemover === null) {
                $this->_whiteSpaceRemover = new Reed_WhiteSpaceRemover();
            }
            if ($this->_dumped) {
                $this->_whiteSpaceRemover->start();
            }
        } else {
            if ($this->_whiteSpaceRemover !== null) {
                $this->_whiteSpaceRemover->stop();
            }
            $this->_whiteSpaceRemover = null;
        }
    }
}

/*
 * Static initializer -- This lines means that it is unnessary to explicitly
 * initialize a conductor page.
 */ 
Page::init();
