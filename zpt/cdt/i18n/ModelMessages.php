<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\i18n;

use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\AbstractModelGenerator;
use \zpt\pct\CodeTemplateParser;

/**
 * This class encapsulates model info required by Conductor for compiling the
 * website.  All info can be specified as an annotation on the model.  This
 * class compiles all info used by conductor for a model and provides defaults
 * for anything unspecified.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelMessages extends AbstractModelGenerator {

  protected static $actorNamespace = 'zeptech\dynamic\i18n';

  protected function getTemplatePath() {
    return __DIR__ . '/ModelMessages.tmpl.php';
  }

  protected function getValuesForModel(Model $model) {
    $modelDisplay = new ModelDisplayParser($model);

    return array(
      'sigular' => $modelDisplay->getSingular(),
      'plural'  => $modelDisplay->getPlural(),
      'article' => $modelDisplay->getArticle()
    );
  }
}
