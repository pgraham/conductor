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
namespace conductor\compile;

use \conductor\widget\LoginForm;
use \conductor\Resource;

use \reed\WebSitePathInfo;

/**
 * This Class performs compilation for {@link LoginForm} instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class LoginCompiler implements Compiler {

  private $_loginForm;

  public function __construct(LoginForm $loginForm) {
    $this->_loginForm = $loginForm;
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $resources = $this->_loginForm->getResources();

    $resources['css']->compile($pathInfo);
    $resources['js']->compile($pathInfo);
  }
}
