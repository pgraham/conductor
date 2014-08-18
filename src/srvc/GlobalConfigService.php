<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\srvc;

use zpt\rest\BaseRequestHandler;
use zpt\rest\RestException;
use zpt\rest\Request;
use zpt\rest\Response;
use zpt\cdt\di\InitializingBean;
use zpt\cdt\rest\BeanRequestHandler;
use zpt\opal\CompanionLoader;
use zpt\orm\Criteria;

/**
 * This class provides a remote service for retrieving and updating global
 * configuration values.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Uri /config
 * @Uri /config/{name}
 */
class GlobalConfigService extends BaseRequestHandler
		implements BeanRequestHandler
{

	/** @Injected */
	private $authProvider;

	private $companionLoader;

	private $mappings;

	/**
	 * @ctorArg( ref = companionLoader )
	 */
	public function __construct(CompanionLoader $companionLoader = null) {
		if ($companionLoader === null) {
			$companionLoader = new CompanionLoader();
		}
		$this->companionLoader = $companionLoader;
	}

	public function get(Request $request, Response $response) {
		$configName = $request->getParameter('name');
		if ($configName === null) {
			// Get all global configuration values
			$persister = $this->companionLoader->get(
				'zpt\dyn\orm\persister',
				'zpt\cdt\model\ConfigValue'
			);
			$c = new Criteria();
			$c->addSelect('name')->addSelect('value')
				->addEquals('editable', true)
				->addSort('name');
			$globalConfig = $persister->retrieve($c);

			$transformer = $this->companionLoader->get(
				'zpt\dyn\orm\transformer',
				'zpt\cdt\model\ConfigValue'
			);
			$response->setData($transformer->asCollection($globalConfig));
			return;
		}
	}

	public function put(Request $request, Response $response) {
		if (!$this->authProvider->hasPermission('cdt-admin')) {
			throw new RestException(401);
		}

		$configName = $request->getParameter('name');
		if ($configName === null) {
			throw new RestException(405, array( 'Allow: GET' ));
		}

		$configValue = $request->getData();

		$c = new Criteria();
		$c->addEquals('name', $configName);

		$persister = $this->companionLoader->get(
			'zpt\dyn\orm\persister',
			'zpt\cdt\model\ConfigValue'
		);
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

	public function getMappings() {
		return $this->mappings;
	}

	public function setAuthProvider($authProvider) {
		$this->authProvider = $authProvider;
	}

	public function setMappings(array $mappings) {
		$this->mappings = $mappings;
	}
}
