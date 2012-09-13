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
    $displayName = $model->getDisplayName();
    if ($displayName === null) {
      $actorParts = explode('_', $model->getActor());
      $displayName = array_pop($actorParts);
    }
    $displayName = strtolower($displayName);

    $displayNamePlural = $model->getDisplayNamePlural();
    if ($displayNamePlural === null) {
      $displayNamePlural = $displayName . 's';
    }
    $displayNamePlural = strtolower($displayNamePlural);

    $article = $model->getDisplayArticle();
    if ($article === null) {
      $article = strstr('aeiou', substr($displayName, 0, 1))
        ? 'an'
        : 'a';
    }

    return array(
      'display' => $displayName,
      'plural'  => $displayNamePlural,
      'article' => $article
    );
  }
}
