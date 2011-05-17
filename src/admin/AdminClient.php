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
 * @package conductor/admin
 */
namespace conductor\admin;

use \clarinet\model\Model;

use \conductor\generator\CrudServiceGenerator;
use \conductor\generator\CrudServiceModelDecorator;
use \conductor\generator\CrudServiceModelDecoratorFactory;
use \conductor\model\DecoratedModel;
use \conductor\model\ModelSet;
use \conductor\script\ServiceProxy;
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
 * @package conductor/admin
 */
class AdminClient extends Composite implements BodyItem {

  const FONT_PATH = 'http://fonts.googleapis.com/css?family=Allerta';

  private $_resources = array();

  /**
   * Create a new Javascript element for the conductor admin client.
   *
   * If debug mode is enabled, then the script will generated using the site's
   * specified model classes.
   *
   * @param array $models Array of clarinet\model\Models for which to build the
   *   client
   * @param WebSitePathInfo $pathInfo Information about the web site's directory
   *   structure, in which the client and it's supporting files exist (or should
   *   exist if in DEBUG mode).
   */
  public function __construct(ModelSet $models, WebSitePathInfo $pathInfo) {
    // Decorate models
    $models->decorate(new CrudServiceModelDecoratorFactory());
    $models->decorate(new AdminModelDecoratorFactory());

    // Compile the admin client.
    $templateValues = $this->_compile($models, $pathInfo);

    $this->initElement(new Div('cdt-Admin'));
    $menu = new Div('menu');
    $menu->add(new ListEl(BaseList::UNORDERED));
    $this->elm->add($menu);
    $ctnt = new Div('ctnt');
    $this->elm->add($ctnt);
    $this->elm->add(new Anchor($pathInfo->getWebRoot(), 'View Site'));

    // Add Client-side model extensions and CRUD service proxies for each of the
    // models.
    foreach ($models AS $model) {
      if ($model->getClientModel() !== null) {
        $this->_resources[] = new Resource($model->getClientModel(), $pathInfo);
      }

      $serviceClass = $model->getCrudServiceClass();
      $this->_resources[] = new ServiceProxy($serviceClass, $pathInfo);
    }

    // Generate support code for updating configuration values.
    $configValueModel = new DecoratedModel(
      new Model('conductor\model\ConfigValue'));
    $configValueModel->decorate(new CrudServiceModelDecorator());

    if (defined('DEBUG') && DEBUG === true) {
      $generator = new CrudServiceGenerator($configValueModel);
      $generator->generate($pathInfo);

    }

    $this->_resources[] = new ServiceProxy(
      $configValueModel->getCrudServiceClass(), $pathInfo);

    $this->_resources[] = new Resource('grid.js', $pathInfo);
    $this->_resources[] = new Resource('tabbedDialog.js', $pathInfo);
    $this->_resources[] = new Resource('conductor-admin.js', $pathInfo,
      $templateValues);
    $this->_resources[] = new Resource('conductor-admin.css', $pathInfo);
  }

  public function getResources() {
    return $this->_resources;
  }

  private function _compile(ModelSet $models, WebSitePathInfo $pathInfo) {
    if (!defined('DEBUG') || DEBUG !== true) {
      return null;
    }

    foreach ($models AS $model) {
      // Generate a crud service for each model.
      $generator = new CrudServiceGenerator($model);
      $generator->generate($pathInfo);
    }

    // Generate the admin client
    $builder = new AdminBuilder($models);
    $templateValues = $builder->build();

    return $templateValues;
  }
}
