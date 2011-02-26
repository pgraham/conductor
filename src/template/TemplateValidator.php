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
 * @package conductor/template
 */
namespace conductor\template;

use \Oboe\ElementComposite;
use \Oboe\Item;

/**
 * This class validates a Template implementation and throws an Exception if any
 * problems are found.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package conductor/template
 */
class TemplateValidator {

  private $_template;

  public function __construct(PageTemplate $template) {
    $this->_template = $template;
  }

  public function validate() {
    // Template can't be nullified
    if ($this->_template === null) {
      throw new Exception('Cannot set a null template');
    }

    // Make sure a content container is defined and that it is an instance
    // of Oboe_ElementBase that implements Oboe_Item_Body
    $cc = $this->_template->getContentContainer();
    if ($cc === null) {
      throw new Exception('A template\'s content container can not be null');
    }
    if (!($cc instanceof ElementComposite) || !($cc instanceof Item\Body)) {
      throw new Exception('A template\'s content container must be'
        .' an instance of Oboe\ElementComposite that implements'
        .' Oboe\Item\Body');
    }

    // If a debug container is defined make sure that it is an instance of
    // Oboe_ElementBase that implements Oboe_Item_Body
    $dc = $this->_template->getDebugContainer();
    if ($dc !== null) {
      if (!($dc instanceof ElementComposite) || !($dc instanceof Item\Body)) {
        throw new Exception('A template\'s debug container must be'
          .' an instance of Oboe\ElementComposite that implements'
          .' Oboe\Item\Body');
      }
    }
  }
}
