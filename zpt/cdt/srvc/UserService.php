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

  /** @Injected */
  private $_authProvider;

  public function post(Request $request, Response $response) {
    $userId = $request->getParameter('userId');

    if ($userId !== 'current') {
      throw new ResetException(401);
    }

    $data = $request->getData();
    $fieldMsgs = array();
    
    if (!$this->_authProvider->checkPassword(
        $data[self::CURRENT_PASSWORD_FIELD]))
    {
      $msg = _L('users.password.currentInvalid');
      $fieldMsgs[self::CURRENT_PASSWORD_FIELD] = $msg;
    }

    $newPw = $data[self::NEW_PASSWORD_FIELD];
    if (!$newPw) {
      $msg = _L('users.password.noPassword');
      $fieldMsgs[self::NEW_PASSWORD_FIELD] = $msg;
    }

    $confirm = $data[self::CONFIRM_PASSWORD_FIELD];
    if ($newPw !== $confirm) {
      $msg = _L('users.password.noMatch');
      $fieldMsgs[self::CONFIRM_PASSWORD_FIELD] = $msg;
    }

    if (count($fieldMsgs) === 0) {
      $this->_authProvider->updatePassword($newPw);
      $msg = _L('users.password.success');
    } else {
      $msg = _L('users.password.failure');
    }

    $response->setData(array(
      'success' => count($fieldMsgs) === 0,
      'msg' => $msg,
      'fieldMsgs' => $fieldMsgs
    ));
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }
}
