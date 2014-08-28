<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\auth;

use zpt\cdt\model\Session;
use zpt\cdt\model\User;
use zpt\orm\Criteria;
use zpt\orm\Repository;

/**
 * This class manages session objects.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SessionManager
{

	/**
	 * The default amount of time for which an inactive session remains valid.
	 */
	const DEFAULT_SESSION_TTL = 3600; // 60 * 60 = 1 hour in seconds

	/**
	 * Constant which when passed to loadSession will cause a loaded session to
	 * be discarded if it has already been authenticated.  This is used when an
	 * authentication attempt is successful in order to reuse an existing,
	 * unauthenticated session.
	 */
	const NEW_IF_AUTHENTICATED = true;

	/* Characters from which a session id prefix will be randomly generated */
	private static $keyPrefixChars = "abcdefghijklmnopqrstuvwxyz0123456789";

	/* ORM Repository which manages Session entities. */
	private $orm;

	/**
	 * Create a SessionManager for {@link Session} entities stored in the given
	 * repository.
	 *
	 * @param Repository $orm
	 */
	public function __construct(Repository $orm) {
		$this->orm = $orm;
	}

	/**
	 * Loads the session with the given session key if it exists.  If the session
	 * does not exist or the session is expired then a new session is returned.
	 *
	 * @param string $sessionKey
	 * @param boolean $newIfAuthenticated
	 *   Force logout if session is attached to a user and it not expired
	 * @return zpt\cdt\model\Session|null
	 *   Return the session with the given key or null the session has expired or
	 *   does not exist.
	 */
	public function loadSession($sessionKey, $newIfAuthenticated = false) {
		if ($sessionKey === null) {
			return $this->newSession();
		}

		$persister = $this->orm->getPersister('zpt\cdt\model\Session');

		$c = new Criteria();
		$c->addEquals('sess_key', $sessionKey);

		$session = $persister->retrieveOne($c);
		if ($session === null) {
			return $this->newSession();
		}

		if ($session->isExpired(self::DEFAULT_SESSION_TTL)) {
			return $this->newSession();
		}

		if ($newIfAuthenticated && $session->getUser() !== null) {
			return $this->newSession();
		}

		$session->setLastAccess(time());
		$persister->save($session);
		return $session;
	}

	/**
	 * Initialize a new session a return its instance.
	 *
	 * @param zpt\cdt\model\User $user The user with which to associate the
	 *	 session
	 * @return new session instance
	 */
	public function newSession(User $user = null) {
		$session = new Session();
		if ($user !== null) {
			$session->setUser($user);
		}

		$prefix = '';
		for ($i = 0; $i < 5; $i++) {
			$char = self::$keyPrefixChars[mt_rand(0, 35)];
			if (mt_rand(0, 1)) {
				$char = strtoupper($char);
			}
			$prefix .= $char;
		}
		$key = uniqid($prefix, true);
		$session->setKey($key);

		$this->orm->getPersister('zpt\cdt\model\Session')->save($session);

		// Send the session key to the client
		$path = _P('/');
		setcookie('conductorsessid', $session->getKey(), 0, $path);

		return $session;
	}
}
