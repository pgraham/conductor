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
namespace zpt\cdt\bin;

require_once __DIR__ . '/../../../bin/common.php';

use \Psr\Log\LogLevel;
use \Psr\Log\AbstractLogger;
use \Psr\Log\LoggerInterface;
use \zpt\util\StringUtils;

/**
 * Psr\Log\LoggerInterface implementation for bin scripts to log to the command
 * line.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CmdLnPsrLoggerImpl extends AbstractLogger implements LoggerInterface
{

	private $showDebug = false;

	public function log($level, $message, array $context = []) {
		$depth = $this->getDepth($context);

		switch ($level) {
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
			binLogError(StringUtils::format($message, $context), $depth);
			break;

			case LogLevel::WARNING:
			binLogWarning(StringUtils::format($message, $context), $depth);
			break;

			case LogLevel::NOTICE:
			binLogSuccess(StringUtils::format($message, $context), $depth);
			break;

			case LogLevel::INFO:
			binLogInfo(StringUtils::format($message, $context), $depth);
			break;

			case LogLevel::DEBUG:
			if ($showDebug) {
				binLogInfo(StringUtils::format("DEBUG: $message", $context), $depth);
			}
			break;

			default:
			assert("/* Invalid log level $level */ false;");
		}
	}

	public function setShowDebug($showDebug) {
		$this->showDebug = (bool) $showDebug;
	}

	private function getDepth($context) {
		if (isset($context['depth'])) {
			return $context['depth'];
		}
		return 0;
	}

}
