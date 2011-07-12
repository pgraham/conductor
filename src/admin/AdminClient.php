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
use \conductor\generator\CrudServiceGenerator;
use \conductor\generator\CrudServiceInfo;
use \conductor\script\ServiceProxy;
use \conductor\Conductor;
use \conductor\Resource;

use \oboe\item\Body as BodyItem;
use \oboe\Anchor;
use \oboe\BaseList;
use \oboe\Composite;
use \oboe\Div;
use \oboe\ListEl;

use \reed\WebSitePathInfo;

/**
 * This class encapsulates the client side component of conductor's admin
 * interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AdminClient extends Composite implements BodyItem {

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

    $this->initElement(new Div('cdt-Admin'));
    $menu = new Div('menu');
    $menu->add(new ListEl(BaseList::UNORDERED));
    $this->elm->add($menu);
    $ctnt = new Div('ctnt');
    $this->elm->add($ctnt);
    $this->elm->add(new Anchor($pathInfo->getWebRoot(), 'View Site'));

    $logout = new Anchor('#', 'Logout');
    $logout->setId('logout');
    $logout->setStyle('margin-left', '20px');
    $this->elm->add($logout);

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
    $this->_resources[] = new Resource('grid.js');
    $this->_resources[] = new Resource('tabbedDialog.js');
    $this->_resources['admin'] = new Resource('conductor-admin.js');
  }

  public function addToPage() {
    $configValueModel = ModelParser::getModel('conductor\model\ConfigValue');

    if (Conductor::isDebug()) {
      $pathInfo = Conductor::$config['pathInfo'];

      // TODO CRUD service compilation needs to be restructured so that it isn't
      //      necessary to do this here
      // -----------

      // Ensure that CRUD services are compiled.
      $generator = new CrudServiceGenerator($configValueModel);
      $generator->generate($pathInfo);
      foreach ($this->_models AS $modelConfig) {
        if (!$modelConfig->hasAdmin()) {
          continue;
        }

        // Generate a crud service for each model.
        $model = ModelParser::getModel($modelConfig->getModelName());
        $generator = new CrudServiceGenerator($model);
        $generator->generate($pathInfo);
      }

      // -----------

      $this->compile($pathInfo, array(
        'models' => $this->_models
      ));
    }

    // TODO Create a ResourceSet class.  This class can then implement a static
    ///     method to retrieve a resource set of resources to include when
    //      performing an explicit compile.  That way there is no concern with
    //      service proxies interfering
    // -------------

    // Add CRUD service proxies to the resource list here so that they don't
    // interfere with explicit compilation
    foreach ($models AS $modelConfig) {
      if (!$modelConfig->hasAdmin()) {
        continue;
      }

      $model = ModelParser::getModel($modelConfig->getModelName());
      $crudInfo = new CrudServiceInfo($model);

      $serviceClass = $crudInfo->getCrudServiceClass();
      $this->_resources[] = new ServiceProxy($serviceClass, $pathInfo);
    }

    // Generate support code for updating configuration values.
    $configValueCrudInfo = new CrudServiceInfo($configValueModel);
    $this->_resources[] = new ServiceProxy(
      $configValueCrudInfo->getCrudServiceClass(), $pathInfo);

    // -------------

    foreach ($this->_resources AS $resource) {
      $resource->addToPage();
    }

    $this->addToBody();
  }

  public function compile(WebSitePathInfo $pathInfo, array $values = null) {
    $compiler = new AdminCompiler($this);
    $compiler->compile($pathInfo, $values);
  }

  public function getResources() {
    return $this->_resources;
  }
}
