<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\crud;

use zpt\orm\model\Model;
use zpt\orm\model\ModelFactory;
use \zpt\cdt\di\Injector;
use \zpt\cdt\i18n\ModelDisplayParser;
use \zpt\orm\BaseModelCompanionDirector;

/**
 * This class encapsulates information about a CRUD remote service for a model
 * class.
 *
 * TODO This class should only be used during compilation, update it to match
 *      the interface of the clarinet model actors
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CrudServiceCompanionDirector extends BaseModelCompanionDirector {

	public function __construct(ModelFactory $modelFactory = null) {
		parent::__construct('crud', $modelFactory);
	}

	protected function getTemplatePath() {
		return __DIR__ . '/CrudService.tmpl.php';
	}

	protected function getValuesForModel(Model $model) {
		$modelStrings = new ModelDisplayParser($model);
		$plural = $modelStrings->getPlural();

		$url = "/$plural";

		$classname = $model->getClass();
		if (preg_match('/^zpt\\\\mod\\\\([^\\\\]+)\\\\model\\\\/', $classname, $matches)) {
			$url = "/$matches[1]$url";
		}

		$gatekeeper = $model->getGatekeeper();
		if ($gatekeeper === null) {
			$gatekeeper = 'zpt\cdt\crud\DefaultGatekeeper';
		}
		
		return array(
			'auth'						 => $model->getAuth(),
			'cdtOrderTokenKey' => 'updateOrderToken_' . str_replace('\\', '_', get_class($model)),
			'gatekeeper'			 => $model->getGatekeeper(),
			'gatekeeperBeanId' => Injector::generateBeanId($gatekeeper),
			'singular'				 => $modelStrings->getSingular(),
			'plural'					 => $plural,
			'idColumn'				 => $model->getId()->getColumn(),
			'url'							 => $url
		);
	}
}
