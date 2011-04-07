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
use \conductor\model\DecoratedModel;
use \conductor\model\ModelSet;
use \conductor\script\ServiceProxy;
use \conductor\Conductor;

use \oboe\head\Javascript;
use \oboe\head\StyleSheet;
use \oboe\item\Body as BodyItem;
use \oboe\Anchor;
use \oboe\BaseList;
use \oboe\Composite;
use \oboe\Div;
use \oboe\ListEl;

use \reed\FsToWebPathConverter;
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

  private $_css;
  private $_js = array();

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

    // We need to know CrudService information about the model in order to
    // load its proxy
    $models->decorate(new CrudServiceModelDecoratorFactory());

    $webPath = $pathInfo->getWebAccessibleTarget();

    if (defined('DEBUG') && DEBUG === true) {
      // Generate a crud service for each model.
      foreach ($models AS $model) {
        $generator = new CrudServiceGenerator($model);
        $generator->generate($pathInfo);
      }

      // Generate the admin client
      $generator = new AdminGenerator($models);
      $generator->generate($pathInfo);
    }

    // Add CRUD service proxies for each of the models
    foreach ($models AS $model) {
      $serviceClass = $model->getCrudServiceClass();

      $this->_js[] = new ServiceProxy($serviceClass, $pathInfo);
    }

    $this->_js[] = new Javascript($webPath . '/js/conductor-admin.js');
    $this->_css = new StyleSheet($webPath . '/css/conductor-admin.css');
  }

  public function getScripts() {
    return $this->_js;
  }

  public function getStyleSheets() {
    return Array( $this->_css, new StyleSheet(self::FONT_PATH) );
  }
}
