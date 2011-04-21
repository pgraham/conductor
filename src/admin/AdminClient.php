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

use \conductor\generator\CrudServiceGenerator;
use \conductor\generator\CrudServiceModelDecoratorFactory;
use \conductor\model\ModelSet;
use \conductor\script\ServiceProxy;
use \conductor\Resource;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;
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
    $this->initElement(new Div('cdt-Admin'));
    $menu = new Div('menu');
    $menu->add(new ListEl(BaseList::UNORDERED));
    $this->elm->add($menu);

    $ctnt = new Div('ctnt');
    $this->elm->add($ctnt);

    $this->elm->add(new Anchor($pathInfo->getWebRoot(), 'View Site'));

    // Decorate models
    $models->decorate(new CrudServiceModelDecoratorFactory());
    $models->decorate(new AdminModelDecoratorFactory());

    $webPath = $pathInfo->getWebAccessibleTarget();
    $jsOutputDir = $pathInfo->getWebTarget() . '/js';

    $templateValues = null;
    if (defined('DEBUG') && DEBUG === true) {
      foreach ($models AS $model) {
        // Generate a crud service for each model.
        $generator = new CrudServiceGenerator($model);
        $generator->generate($pathInfo);

        // Copy any client side model extensions to the web target
        if ($model->getClientModel() !== null) {
          $src = $model->getClientModel();
          $dest = $jsOutputDir . "/{$model->getActor()}.js";          

          copy($src, $dest);
        }
      }

      // Generate the admin client
      $builder = new AdminBuilder($models);
      $templateValues = $builder->build();
    }

    // Add Client-side model extensions
    foreach ($models AS $model) {
      if ($model->getClientModel() !== null) {
        $this->_resources[] = new Javascript(
          $pathInfo->fsToWeb("$jsOutputDir/{$model->getActor()}.js"));
      }
    }

    // Add CRUD service proxies for each of the models
    foreach ($models AS $model) {
      $serviceClass = $model->getCrudServiceClass();

      $this->_resources[] = new ServiceProxy($serviceClass, $pathInfo);
    }

    $this->_resources[] = new Resource('grid.js', $pathInfo);
    $this->_resources[] = new Resource('tabbedDialog.js', $pathInfo);
    $this->_resources[] = new Resource('conductor-admin.js', $pathInfo,
      $templateValues);

    $this->_resources[] = new Resource('conductor-admin.css', $pathInfo);
  }

  public function getResources() {
    return $this->_resources;
  }
}
