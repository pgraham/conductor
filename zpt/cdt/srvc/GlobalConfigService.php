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
namespace zpt\cdt\srvc;

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\Transformer;
use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RestException;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;

/**
 * This class provides a remote service for retrieving and updating global
 * configuration values.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Uri /config
 * @Uri /config/{name}
 */
class GlobalConfigService extends BaseRequestHandler implements RequestHandler {

  /** @Injected */
  private $_authProvider;

  public function get(Request $request, Response $response) {
    $configName = $request->getParameter('name');
    if ($configName === null) {
      // Get all global configuration values
      // TODO - Inject this
      $persister = Persister::get('zpt\cdt\model\ConfigValue');
      $c = new Criteria();
      $c->addSelect('name')->addSelect('value')
        ->addEquals('editable', true)
        ->addSort('name');
      $globalConfig = $persister->retrieve($c);

      $transformer = Transformer::get('zpt\cdt\model\ConfigValue');
      $response->setData($transformer->asCollection($globalConfig));
      return;
    }
  }

  public function put(Request $request, Response $response) {
    if (!$this->_authProvider->hasPermission('cdt-admin')) {
      throw new RestException(401);
    }

    $configName = $request->getParameter('name');
    if ($configName === null) {
      throw new RestException(405, array( 'Allow: GET' ));
    }

    $configValue = $request->getData();

    $c = new Criteria();
    $c->addEquals('name', $configName);

    $persister = Persister::get('zpt\cdt\model\ConfigValue');
    $model = $persister->retrieveOne($c);
    if ($model === null) {
      throw new RestException(404);
    }

    $model->setValue($configValue);
    $persister->save($model);

    $response->setData(array(
      'success' => true,
      'msg' => "$configName has been updated"
    ));
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }
}
