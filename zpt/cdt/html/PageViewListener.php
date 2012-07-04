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
 */
namespace zpt\cdt\html;

/**
 * Interface for classes that want to be informed when a page is viewed.  If
 * made available through DI then they will be wired and invoked automatically
 * whenever an HtmlProvider instance is used to handle a request.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface PageViewListener {

  public function pageView();

}
