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
namespace conductor\modeling;

use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\AbstractGenerator;
use \zpt\pct\CodeTemplateParser;

/**
 * This class encapsulates model info required by Conductor for compiling the
 * website.  All info can be specified as an annotation on the model.  This
 * class compiles all info used by conductor for a model and provides defaults
 * for anything unspecified.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelInfo extends AbstractGenerator {

  private static $_cache = array();

  /**
   * Retrieve a ModelInfo instance for the specified model.  This ModelInfo
   * class must already be generated.
   *
   * @param string $modelName
   */
  public static function get($modelName) {
    if (!array_key_exists($modelName, self::$_cache)) {
      $actor = str_replace('\\', '_', $modelName);
      $fq = "zeptech\\dynamic\\info\\$actor";
      self::$_cache[$modelName] = new $fq();
    }
    return self::$_cache[$modelName];
  }

  /*
   * ===========================================================================
   * Generator
   * ===========================================================================
   */

  private $_tmpl;

  public function __construct($outputPath) {
    parent::__construct($outputPath . '/zeptech/dynamic/info');

    $parser = new CodeTemplateParser();
    $this->_tmpl = $parser->parse(
      file_get_contents(__DIR__ . '/modelInfo.tmpl.php'));
  }

  protected function _generate(Model $model) {
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

    return $this->_tmpl->forValues(array(
      'actor'   => $model->getActor(),
      'display' => $displayName,
      'plural'  => $displayNamePlural,
      'article' => $article
    ));
  }
}
