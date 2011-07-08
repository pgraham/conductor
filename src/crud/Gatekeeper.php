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
namespace conductor\crud;

/**
 * Interface for model gatekeepers.  Gatekeepers are responsible for performing
 * permissions checks for the four CRUD operations.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Gatekeeper {

  /**
   * Indicates whether or not the current user is allowed to create the given
   * model.
   *
   * @param model $model The model for which create authorization is requested.
   * @return boolean
   */
  public function canCreate($model);

  /**
   * Indicates whether or not the current user is allowed to delete the given
   * model.
   *
   * @param model $model The model for which create authorization is requested.
   * @return boolean
   */
  public function canDelete($model);

  /**
   * Indicates whether or not the current user is allowed to read the given
   * model.
   *
   * @param model $model The model for which create authorization is requested.
   * @return boolean
   */
  public function canRead($model);

  /**
   * Indicates whether or not the current user is allowed to write the given
   * model.
   *
   * @param model $model The model for which create authorization is requested.
   * @return boolean
   */
  public function canWrite($model);

  /**
   * Check that the current user is allowed to create the given model.
   *
   * @param model $model The model for which create authorization is requested.
   * @throws conductor\auth\AuthorizationException if the user is not allowed to
   *   create the given model.
   */
  public function checkCanCreate($model);

  /**
   * Check that the current user is allowed to delete the given model.
   *
   * @param model $model The model for which delete authorization is requested.
   * @throws conductor\auth\AuthorizationException if the user is not allowed to
   *   delete the given model.
   */
  public function checkCanDelete($model);

  /**
   * Check that the current user is allowed to read the given model.
   *
   * @param model $model The model for which read authorization is requested.
   * @throws conductor\auth\AuthorizationException if the user is not allowed to
   *   read the given model.
   */
  public function checkCanRead($model);

  /**
   * Check that the current user is allowed to write the given model.
   *
   * @param model $model The model for which write authorization is requested.
   * @throws conductor\auth\AuthorizationException if the user is not allowed to
   *   write the given model.
   */
  public function checkCanWrite($model);

}
