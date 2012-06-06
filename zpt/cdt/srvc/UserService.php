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
namespace zpt\cdt\srvc;

use \conductor\Auth;
use \zeptech\rest\BaseRequestHandler;
use \zeptech\rest\RequestHandler;
use \zeptech\rest\Request;
use \zeptech\rest\Response;
use \zeptech\rest\RestException;

/**
 * This class provides a remote interface for user management.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Uri /users/{userId}/password
 */
class UserService extends BaseRequestHandler implements RequestHandler {

  const CURRENT_PASSWORD_FIELD = 'curPw';
  const NEW_PASSWORD_FIELD = 'newPw';
  const CONFIRM_PASSWORD_FIELD = 'confirmPw';

  public function post(Request $request, Response $response) {
    $userId = $request->getParameter('userId');

    if ($userId !== 'current') {
      throw new ResetException(401);
    }

    $data = $request->getData();
    $success = true;
    $fieldMsgs = array();
    
    if (!Auth::checkPassword($data[self::CURRENT_PASSWORD_FIELD])) {
      $success = false;
      $msg = _L('users.password.currentInvalid');
      $fieldMsgs[self::CURRENT_PASSWORD_FIELD] = $msg;
    }

    $newPw = $data[self::NEW_PASSWORD_FIELD];
    if (!$newPw) {
      $success = false;
      $msg = _L('users.password.noPassword');
      $fieldMsgs[self::NEW_PASSWORD_FIELD] = $msg;
    }

    $confirm = $data[self::CONFIRM_PASSWORD_FIELD];
    if ($newPw !== $confirm) {
      $success = false;
      $msg = _L('users.password.noMatch');
      $fieldMsgs[self::CONFIRM_PASSWORD_FIELD] = $msg;
    }

    if ($success) {
      Auth::updatePassword($newPw);
      $msg = _L('users.password.success');
    } else {
      $msg = _L('users.password.failure');
    }

    $response->setData(array(
      'success' => $success,
      'msg' => $msg,
      'fieldMsgs' => $fieldMsgs
    ));
  }
}
