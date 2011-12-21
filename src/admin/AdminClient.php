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
namespace conductor\admin;

use \clarinet\model\Model;
use \clarinet\model\Parser as ModelParser;

use \conductor\compile\AdminCompiler;
use \conductor\ServiceProxy;
use \conductor\Conductor;
use \conductor\Resource;

use \oboe\Composite;
use \oboe\Element;

use \reed\File;
use \reed\WebSitePathInfo;

/**
 * This class encapsulates the client side component of conductor's admin
 * interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminClient extends Composite {

  const FONT_PATH = 'http://fonts.googleapis.com/css?family=Allerta';

  private $_models;

  private $_resources = array();

  /**
   * Create a new Javascript element for the conductor admin client.
   *
   * If debug mode is enabled, then the script will generated using the site's
   * specified model classes.
   *
   * @param array $models Array of ModelConfig objects representing the models
   *   to build into the client.
   * @param WebSitePathInfo $pathInfo Information about the web site's directory
   *   structure, in which the client and it's supporting files exist (or should
   *   exist if in DEBUG mode).
   */
  public function __construct(array $models, WebSitePathInfo $pathInfo) {
    $this->_models = $models;

    $this->initElement(
      Element::div()
        ->setId('cdt-Admin')
        ->add(
          Element::div()
            ->setId('menu')
            ->add(Element::ul())
        )
        ->add(
          Element::div()
            ->setId('ctnt')
        )
        ->add(Element::a($pathInfo->getWebRoot(), 'View Site'))
        ->add(
          Element::a('#', 'Logout')
            ->setId('logout')
            ->setStyle('margin-left', '20px')
        )
    );

    // Add Client-side model extensions and CRUD service proxies for each of the
    // models.
    foreach ($models AS $modelConfig) {
      if (!$modelConfig->hasAdmin()) {
        continue;
      }

      $model = ModelParser::getModel($modelConfig->getModelName());

      $adminInfo = new AdminModelInfo($model);
      if ($adminInfo->getClientModel() !== null) {
        $this->_resources[] = new Resource($adminInfo->getClientModel());
      }
    }

    $this->_resources[] = new Resource('conductor-admin.css');
    $this->_resources[] = new Resource($pathInfo->getLibPath()
      . '/jslib/jquery-ui/themes/base/jquery.ui.grid.css');
    $this->_resources[] = new Resource('grid.js');
    $this->_resources[] = new Resource('tabbedDialog.js');
    $this->_resources['admin'] = new Resource('conductor-admin.js');
  }

  public function addToPage() {
    if (Conductor::isDebug()) {
      $this->compile(array(
        'models' => $this->_models
      ));
    }

    // Add CRUD service proxies to the resource list here so that they don't
    // interfere with explicit compilation
    foreach ($this->_models AS $modelConfig) {
      if (!$modelConfig->hasAdmin()) {
        continue;
      }
      $this->_resources[] = ServiceProxy::getCrud($modelConfig->getModelName());
    }

    // Generate support code for updating configuration values.
    $this->_resources[] = ServiceProxy::getCrud('conductor\model\ConfigValue');

    foreach ($this->_resources AS $resource) {
      if ($resource instanceof \oboe\Javascript) {
        $resource->addToHead();
      } else {
        $resource->addToPage();
      }
    }

    $this->addToBody();
  }

  public function compile(array $values = null) {
    $compiler = new AdminCompiler($this);
    $compiler->compile(Conductor::getPathInfo(), $values);
  }

  public function getResources() {
    return $this->_resources;
  }
}
