<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\i18n;

use \zeptech\orm\generator\model\Model;
use \zpt\pct\CodeTemplateParser;
use \zpt\orm\ModelCompanionGenerator;

/**
 * This class encapsulates model info required by Conductor for compiling the
 * website.  All info can be specified as an annotation on the model.  This
 * class compiles all info used by conductor for a model and provides defaults
 * for anything unspecified.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelMessages extends ModelCompanionGenerator {

	protected function getCompanionNamespace($defClass) {
		return 'zpt\dyn\i18n';
	}

	protected function getTemplatePath($defClass) {
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
