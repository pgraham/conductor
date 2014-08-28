<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use zpt\cdt\di\InjectedLoggerAwareTrait;
use zpt\cdt\auth\Authorize;
use zpt\cdt\auth\SessionManager;
use zpt\cdt\exception\AuthException;
use zpt\cdt\model\User;
use zpt\cdt\model\Visitor;
use zpt\orm\Criteria;
use zpt\orm\Repository;

use LightOpenId;

/**
 * This class ensures that the requesting user is assigned to a session. The
 * session can optionally be associated with a registered user.
 *
 * @author Philip Graham <philip@lightbox.org>
 */
class AuthProvider implements LoggerAwareInterface {
	//TODO Implement InitializingBean and remove explicit call to init for each
	//method
	use InjectedLoggerAwareTrait;

	private $orm;
	private $sessionManager;

	private $openId;
	private $session;
	private $visitor;

	public function __construct(Repository $orm) {
		$this->logger = new NullLogger();

		$this->orm = $orm;
		$this->sessionManager = new SessionManager($orm);
	}

	public function authenticate($username, $password) {
		$this->logger->info("AUTH: Authentication attempt for $username");
		$pwHash = md5($password);

		$c = new Criteria();
		$c->addEquals('username', $username);
		$c->addEquals('password', $pwHash);

		$persister = $this->getPersister('zpt\cdt\model\User');
		$user = $persister->retrieveOne($c);

		if ($user) {
			$this->logger->info("AUTH: Authentication successful");
		} else {
			$this->logger->info("AUTH: Authentication failed");
		}

		return $user;
	}

	/**
	 * Check if the given password is the password for the current user. Returns
	 * false if there is no current user.
	 *
	 * @param string $password Password to check.
	 * @return boolean
	 */
	public function checkPassword($password) {
		$user = $this->getSession()->getUser();
		if ($user === null) {
			throw new AuthException(AuthException::NOT_LOGGED_IN);
		}

		$pwHash = md5($password);
		return $user->getPassword() === $pwHash;
	}

	/**
	 * Check if the current session has the requested authorization and if not
	 * throw an exception
	 *
	 * @param string $permName
	 * @param string $level Default 'write'
	 */
	public function checkPermission($permName, $level = 'write') {
		if (!$this->hasPermission($permName, $level)) {
			throw new AuthException(AuthException::NOT_AUTHORIZED,
				"$permName:$level");
		}
	}

	/**
	 * Getter for the current openId status.
	 *
	 * This will be null if no openId login attempt has been made.
	 *
	 * @return LightOpenId
	 */
	public function getOpenId() {
		return $this->openId;
	}

	/**
	 * Getter for an already initiated session.
	 *
	 * Do not call this function is a login attempt is being made, use init(...)
	 * instead.
	 *
	 * @return Session
	 */
	public function getSession() {
		$this->init();
		return $this->session;
	}

	/**
	 * Getter for the visitor instance associated with this request.
	 *
	 * TODO Move visitor tracking into a module
	 *
	 * @return Visitor
	 */
	public function getVisitor() {
		$this->init();
		return $this->visitor;
	}

	/**
	 * Determine if the current session has the given permissions.
	 *
	 * @param string $permName The name of a permission resource to check
	 * @param string $level Optional level of permission on the given resource
	 * @return True if the current session has access, false otherwise.
	 */
	public function hasPermission($permName, $level = 'write') {
		$this->init();

		$allowed = false;
		if ($this->session->getUser() !== null) {
			$allowed = Authorize::allowed($this->session->getUser(), $permName, $level);
		}

		$this->logger->debug(
			"AUTH: User " . ($allowed ? 'has' : 'does not have')
			. " `$permName:$level` permission"
		);

		return $allowed;
	}

	/**
	 * Authenticate the user.  The process is as follows.  Check if there is
	 * a login attempt with the request and if so process it.  If there is no
	 * login attempt check if there is opendId authentication info in the request
	 * and process it. If there is no login attempt or openid info then check if
	 * there is a session id in the request's cookie. If there is then check if
	 * the session is valid.	If so, load any permissions associated with the
	 * session. If the session is invalid or there is no session id in the
	 * request, then initiate a new session with default permissions.
	 */
	public function init() {
		$this->initSession();
		$this->initVisitor();
	}

	/**
	 * Process a login request with the given username and password.
	 *
	 * @param string $username An optional username to use to authenticate.
	 * @param string $password An optional password to use to authenticate.
	 */
	public function login($username, $password) {
		// Ensure that a session is set
		$this->init();

		// To save some headaches around ambiguous results, never preserve an
		// already authenticated user.	Otherwise, a failed login attempt could
		// appear to be successful since the session would still be associated
		// with a user.  Another way to consider this is that a login attempt
		// is an implicit logout, whether the login succeeds or not
		if ($this->session->getUser() !== null) {
			$this->session = $this->sessionManager->newSession();
		}

		$user = $this->authenticate($username, $password);
		if ($user !== null) {
			$this->session->setUser($user);

			$persister = $this->getPersister($this->session);
			$persister->save($this->session);
		}
	}

	/**
	 * Logout the current user by deleting the conductor cookie.
	 */
	public function logout() {
		$this->session = null;
		setcookie('conductorsessid', '', time() - 3600, _P('/'));
	}

	/**
	 * Process an OpenId login.  This will handle an initial redirect to an
	 * openid provider or an assertion from an OP
	 *
	 * TODO This is broken FIXME
	 *
	 * @param $identity OpenId identity
	 */
	public function openIdLogin($identity, $returnUrl) {
		require_once dirname(__FILE__) . '/../../lightopenid/openid.php';
		$openId = new LightOpenId(Conductor::getHostName());

		if (!$openId->mode) {
			$openId->identity = $identity;

			header('Location: ' . $openId->authUrl());
			exit;

		} else {

			if ($openId->validate()) {
				// Ensure a session is set
				$this->init();

				// This is a positive assertion.
				// To save some headaches around ambiguous results, never preserve an
				// already authenticated user.	Otherwise, a failed login attempt could
				// appear to be successful since the session would still be associated
				// with a user.  Another way to consider this is that a login attempt
				// is an implicit logout, whether the login succeeds or not.	However,
				// if a current session is unauthenticated, it will be associated with
				// the now logged in user.
				if ($this->session->getUser() !== null) {
					$this->session = $this->sessionManager->newSession();
				}

				// Need to assign a user ID to the session
				$persister = $this->getPersister('zpt\cdt\model\User');
				$c = new Criteria();
				$c->addEquals('oid_identity', $openId->identity);
				$user = $persister->retrieveOne($c);

				if ($user === null) {
					$user = new User();
					$user->setOpenId($openId->identity);
					$persister->save($user);
				}

				$this->session->setUser($user);
				$persister = $this->getPersister($this->session);
				$persister->save($this->session);

				// Redirect to the same page if handling a synchronous request so
				// that a refresh doesn't re-authenticate the same user
				$this->_redirectIf();
			} else {
				$this->session = $this->_getSession();
			}

		}

		$this->openId = $openId;
	}

	/**
	 * Update the password for the current user to the given password.
	 *
	 * @param string $password
	 */
	public function updatePassword($password) {
		$user = $this->getSession()->getUser();
		if ($user === null) {
			// There is no user associated with the current session
			throw new AuthException(AuthException::NOT_LOGGED_IN);
		}

		$pwHash = md5($password);
		$user->setPassword($pwHash);

		$persister = $this->getPersister($user);
		$persister->save($user);
	}

	private function getPersister($model) {
		return $this->orm->getPersister($model);
	}

	/* Initialize the session. */
	private function initSession() {
		if ($this->session !== null) {
			return;
		}

		// Initialize session
		$this->session = $this->sessionManager->loadSession(
			isset($_COOKIE['conductorsessid'])
				? $_COOKIE['conductorsessid']
				: null
		);
	}

	/* Initialize the visitor. */
	private function initVisitor() {
		// Initialize visitor
		if ($this->visitor !== null) {
			return;
		}

		$persister = $this->getPersister('zpt\cdt\model\Visitor');

		$visitor = null;
		if (isset($_COOKIE['visitor_id'])) {
			$visitorKey = $_COOKIE['visitor_id'];

			$c = new Criteria();
			$c->addEquals('key', $visitorKey);

			$visitor = $persister->retrieveOne($c);
			if ($visitor === null) {
				// It is possible under certain circumstances to get here.	An
				// example would be if the visitors table is cleared.  Some failure
				// cases may also result in a cookie getting out of sync with the
				// server.	In this case a new visitor ID should be assigned.
				$visitor = $this->newVisitor($persister);
			}
		} else {
			$visitor = $this->newVisitor($persister);
		}

		$this->visitor = $visitor;

	}

	/* Create a new visitor. */
	private function newVisitor($persister) {
		$visitorKey = uniqid('visitor_', true);

		$visitor = new Visitor();
		$visitor->setKey($visitorKey);
		$persister->save($visitor);

		$tenYearsFromNow = time() + 315569260;
		$path = _P('/');
		setcookie('visitor_id', $visitorKey, $tenYearsFromNow, $path);

		return $visitor;
	}
}
