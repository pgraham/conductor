<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Reed and is licensed by the Copyright holder under the
 * 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Reed
 */
/**
 * This class validates a Reed_Template implementation and throws a
 * Reed_Exception if any problems are found.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package Reed
 */
class Reed_TemplateValidator {

    private $_template;

    public function __construct(Reed_Template $template) {
        $this->_template = $template;
    }

    public function validate() {
        // Template can't be nullified
        if ($this->_template === null) {
            throw new Reed_Exception('Cannot set a null template');
        }

        // Make sure a content container is defined and that it is an instance
        // of Oboe_ElementBase that implements Oboe_Item_Body
        $cc = $this->_template->getContentContainer();
        if ($cc === null) {
            throw new Reed_Exception(
                'A template\'s content container can not be null');
        }
        if (!($cc instanceof Oboe_ElementComposite) ||
            !($cc instanceof Oboe_Item_Body))
        {
            throw new Reed_Exception('A template\'s content container must be'
                .' an instance of Oboe_ElementComposite that implements'
                .' Oboe_Item_Body');
        }

        // If a debug container is defined make sure that it is an instance of
        // Oboe_ElementBase that implements Oboe_Item_Body
        $dc = $this->_template->getDebugContainer();
        if ($dc !== null) {
            if (!($dc instanceof Oboe_ElementComposite) ||
                !($dc instanceof Oboe_Item_Body))
            {
                throw new Reed_Exception('A template\'s debug container must be'
                    .' an instance of Oboe_ElementComposite that implements'
                    .' Oboe_Item_Body');
            }
        }
    }
}
