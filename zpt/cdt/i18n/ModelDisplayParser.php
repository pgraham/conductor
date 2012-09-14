<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\i18n;

use \zeptech\orm\generator\model\Model;

/**
 * This class parses display strings from a model and generates defaults for
 * those that are unspecified.  There are 3 supported display strings:
 *
 * TODO This encapsulates concepts that are not consistent across all languages.
 *      More work needs to be done to internationalize entity type
 *      identification. An idea is to specify a conventional message identifier
 *      so that the defaults can be overridden using the <lang>.messages files.
 *
 * - Display name: Specified with the annotation @Singular this is used to
 *   identify the type of entity in a singular context. Default is
 *   the name of the entity.
 * - Display name plural: Specified with the annotation @Plural this
 *   is used to identify the type of entity in a plural context. Default is the
 *   entity's display name with an 's' character appended.
 * - Display article: Specified with the annotation @Article this is
 *   either the value 'a' or 'an'.  Default is 'a' if the name of the entity
 *   starts with a consonant and 'an' if it starts with a vowel.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelDisplayParser {

  private $_article;
  private $_plural;
  private $_singular;

  /**
   * Parse the display string for the given {@link Model}
   *
   * @param Model $model The model to parse
   */
  public function __construct(Model $model) {
    $singular = $model->getSingular();
    if ($singular === null) {
      $actorParts = explode('_', $model->getActor());
      $singular = array_pop($actorParts);
    }
    $this->_singular = strtolower($singular);

    $plural = $model->getPlural();
    if ($plural === null) {
      $plural = $singular . 's';
    }
    $this->_plural = strtolower($plural);

    $article = $model->getArticle();
    if ($article === null) {
      $article = strstr('aeiou', substr($singular, 0, 1))
        ? 'an'
        : 'a';
    }
    $this->_article = $article;
  }

  /**
   * Getter for the model's singular type identifier.
   *
   * @return string
   */
  public function getSingular() {
    return $this->_singular;
  }

  /**
   * Getter for the model's plural type identifier.
   *
   * @return string
   */
  public function getPlural() {
    return $this->_plural;
  }

  /**
   * Getter for the article to use when refering to an entity in a singular
   * context.
   *
   * @return string
   */
  public function getArticle() {
    return $this->_article;
  }
}
