<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\i18n;

use zpt\orm\model\Model;
use zpt\orm\model\ModelFactory;
use zpt\orm\BaseModelCompanionDirector;

/**
 * This class encapsulates model info required by Conductor for compiling the
 * website.  All info can be specified as an annotation on the model.  This
 * class compiles all info used by conductor for a model and provides defaults
 * for anything unspecified.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelMessagesCompanionDirector extends BaseModelCompanionDirector
{

	public function __construct(ModelFactory $modelFactory = null) {
		parent::__construct('modelmsgs', $modelFactory);
	}

	public function getTemplatePath() {
		return __DIR__ . '/ModelMessages.tmpl.php';
	}

	protected function getValuesForModel(Model $model) {
		$modelDisplay = new ModelDisplayParser($model);

		$values = array(
			'singular' => $modelDisplay->getSingular(),
			'plural'	=> $modelDisplay->getPlural(),
			'article' => $modelDisplay->getArticle()
		);
		return $values;
	}
}
