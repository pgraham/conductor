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
 * This class is an extension that to Oboe_Form_TextInput that allows a text
 * input to be added directly to the body, rather than in a containing form.
 * 
 * This can reduce the amount of markup/styling required for a textbox whose
 * value is only ever submitted using AJAX.
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package Reed
 */
class Reed_Form_AjaxTextInput extends Reed_Form_AjaxInput {

    /**
     * Constructor.
     *
     * @param id - Since this input isn't part of a form a name isn't required
     *             but an id is important for retrieving the element using JS
     * @param value - The textbox's initial value
     */
    public function __construct($id = null, $value = null) {
        $input = new Oboe_Form_TextInput('ajaxbox', $value);
        if ($id !== null) {
            $input->setId($id);
        }

        parent::__construct($input);
    }
}
