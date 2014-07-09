<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\bin;

use \Psr\Log\LogLevel;
use \Psr\Log\AbstractLogger;
use \Psr\Log\LoggerInterface;

/**
 * PSR-3 LoggerInterface implementation that outputs to the command line.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CmdlnLogger extends AbstractLogger implements LoggerInterface
{

	const GREEN = 32;
	const RED = 31;
	const YELLOW = 93;
	const WHITE = 39;

	private $showDebug = false;

	public function log($level, $message, array $context = []) {
		switch ($level) {
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
			$this->printError(String($message)->format($context));
			if ($this->showDebug && isset($context['exception'])) {
				$e = $context['exception'];
				echo "[DEBUG] {$e->getTraceAsString()}\n";

				while($e = $e->getPrevious()) {
					echo "[DEBUG] Caused by: {$e->getMessage()}\n";
					echo "[DEBUG] {$e->getTraceAsSTring()}\n\n";
				}
			}
			break;

			case LogLevel::WARNING:
			$this->printWarning(String($message)->format($context));
			break;

			case LogLevel::NOTICE:
			$this->printSuccess(String($message)->format($context));
			break;

			case LogLevel::INFO:
			$this->printInfo(String($message)->format($context));
			break;

			case LogLevel::DEBUG:
			if ($this->showDebug) {
				if (is_array($message)) {
					$message = implode(' ', $message);
				}
				$message = String($message)->format($context);
				echo "$message\n";
			}
			break;

			default:
			assert("/* Invalid log level $level */ false;");
		}
	}

	public function setShowDebug($showDebug) {
		$this->showDebug = (bool) $showDebug;
	}

	private function printError($msg) {
		$this->printLog($msg, '✖', self::RED);
	}

	private function printInfo($msg) {
		$this->printLog($msg, '➜', self::WHITE);
	}

	private function printSuccess($msg) {
		$this->printLog($msg, '✔', self::GREEN);
	}

	private function printWarning($msg) {
		$this->printLog($msg, '⚠', self::YELLOW);
	}

	private function printLog($msg, $marker, $colour) {
		if (is_array($msg)) {
			$msg = implode(' ', $msg);
		}
		echo " \e[{$colour}m{$marker}\e[0m $msg\n";
	}
}
