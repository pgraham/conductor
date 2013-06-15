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
namespace zpt\cdt\html;

use \zpt\oobo\Body;
use \zpt\oobo\Element;

/**
 * This class uses PHP output buffering to capture any output that occurs
 * durring page construction, prior to page output.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class DebugCatcher {

  /* The captured output */
  private $_captured = '';

  /* Whether or not the captured debug is in the process of being ouput */
  private $_flushing = false;

  /* Whether or not to output captured debug information */
  private $_output = false;

  /**
   * Create a new Debug Catcher.
   */
  public function __construct() {
    ini_set('error_prepend_string', '<phpfatalerror>');
    ini_set('error_append_string', '</phpfatalerror>');
    ob_start(array($this, 'addDebug'));
  }

  /**
   * PHP output buffering callback that does the actual capturing.
   */
  public function addDebug($output) {
    if (!$this->_output) {
      return;
    }

    if (preg_match('|<phpfatalerror>(.*)</phpfatalerror>|s', $output, $m)) {

      // This method doesn't actually get passed any notices or warnings until
      // the output buffer is flushed.  So we use a flag to identify this case.
      // When this case is encountered the flush() is executing so we can append
      // the error text to the captured output to be output in the debug
      // container.
      if ($this->_flushing) {
        $this->_captured .= $output;
        return;
      }

      // If an error has occurred then there is no stack so the only thing
      // we can output is what's returned by this function.
      $errors = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
        . '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"'
        . ' "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'."\n"
        . '<html xmlns="http://www.w3.org/1999/xhtml">'."\n"
        . '<head><title>Fatal Error</title></head>'."\n"
        . '<body><div>';

      if ($this->_output) {
        $errors .= '<p style="font-weight:bold">'.$m[1].'</p>';
        if ($this->_captured !== '') {
          $errors .= '<h1>Debug Information</h1>'."\n";
          $errors .= '<p>'.$this->_captured.'</p>'."\n";
        }
      } else {
        $errors .= '<p>An error has occured, please contact the website'
          .' administrator.</p>';
      }
      $errors.= '</div></body></html';
      return $errors;
    }

    $this->_captured .= nl2br($output);
  }

  /**
   * Output captured debug and stop capturing.
   */
  public function flush() {
    $this->_flushing = true;
    ob_end_clean();
    $this->_flushing = false;

    if ($this->_captured === '' || !$this->_output) {
      return;
    }

    $debug = Element::div()
      ->setId('debugging')
      ->add(Element::h2('Debug Information'))
      ->add(Element::p()->add($this->_captured));

    // Add directly to body instance to avoid any template container
    Body::getInstance()->add($debug);
  }

  /**
   * Get whether or not the captured debug is being output.
   *
   * @return boolean
   */
  public function getOutput() {
    return $this->_output;
  }

  /**
   * Set whether or not to output captured debug.
   *
   * @param boolean
   */
  public function setOutput($output) {
    $this->_output = $output;
  }
}
