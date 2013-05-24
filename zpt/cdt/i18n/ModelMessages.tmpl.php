<?php
namespace /*# actorNs #*/;

/**
 * This is a generated class that provides pre-parsed information about a model
 * that is used by various Conductor classes, generated or otherwise.
 *
 * TODO Separate messages into it's own generated class that can be used for
 *      localization.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class /*# actorClass #*/ {

  public function createSuccessMsg() {
    return ucfirst($this->_successMsg('created', '/*# article #*/')) . '.';
  }

  public function deleteSuccessMsg() {
    return ucfirst($this->_successMsg('deleted', 'the')) . '.';
  }

  public function duplicateMsg($dupField, $dupValue) {
    $msg = "/*# article #*/ /*# singular #*/ with $dupField '$dupValue' already exists";
    return ucfirst($msg) . '.';
  }

  public function genericErrorMsg($action, $plural = false) {
    if ($plural) {
      $msg = "there was an error $action the /*# plural #*/";
    } else {
      $msg = "there was an error $action /*# article #*/ /*# singular #*/";
    }
    $msg .= " Please try again later or contact an administrator if the problem"
          . " persists";
    return ucfirst($msg) . '.';
  }

  public function getDisplayName() {
    return '/*# singular #*/';
  }

  public function getDisplayNamePlural() {
    return '/*# plural #*/';
  }

  public function getIndefiniteArticle() {
    return '/*# article #*/';
  } 

  public function invalidEntityMsg() {
    return "The given /*# singular #*/ is not valid because:";
  }

  public function invalidFilterMsg($filter) {
    $msg = "/*# plural #*/ do not support '$filter' as a filter field";
    return ucfirst($msg) . '.';
  }

  public function invalidSortMsg($sort) {
    $msg = "/*# plural #*/ do not support '$sort' as a sort field";
    return ucfirst($msg) . '.';
  }

  public function notNullMsg($field) {
    $msg = "the '$field' field of /*# article #*/ /*# singular #*/ cannot be blank";
    return ucfirst($msg) . '.';
  }

  public function updateSuccessMsg() {
    return ucfirst($this->_successMsg('updated', 'the')) . '.';
  }

  public function _successMsg($action, $article) {
    return "successfully $action $article /*# singular #*/";
  }

}
