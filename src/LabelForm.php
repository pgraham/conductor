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
namespace conductor;

use \conductor\AjaxInput;
use \oboe\ElementComposite;
use \oboe\form\Input;
use \oboe\item;
use \oboe\Table;

/**
 * This class encapsulates a form that displays groups of input elements
 * as a two column table of label's and input elements.  This form has a minimal
 * implementation and is intended for use with AJAX forms.  As a result it lacks
 * some interface you might expect with a form object such as the ability to
 * specify method and action attributes.  Should they be required these
 * attributes can be specified using the setAttribute(name, value)  method.
 *
 * TODO - Update this class to extend \oboe\Composite
 *
 * @author Philip Graham <philip@lightbox.org>
 * @package conductor
 */
class LabelForm extends ElementComposite implements item\Body {

    private $_tbl;
    private $_cnt;

    /**
     * Create a new LabelledForm object.
     *
     * @param string
     * @param string
     */
    public function __construct($id = null, $class = null) {
        parent::__construct('form', $id, $class);

        $this->_tbl = new Table();
        $this->_elements[] = $this->_tbl;

        $this->_cnt = 0;
    }

    public function addFormItem($lbl, Input $input) {
        $this->_tbl->addCell($this->_cnt, $lbl);
        $this->_tbl->addCell($this->_cnt++, new AjaxInput($input));
    }
}
