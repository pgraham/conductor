<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\cdt\exception;

use \zpt\util\StringUtils;
use \ErrorException;
use \Exception;

/**
 * Top level exception handler for sites that are in debug mode.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TopLevelDebugExceptionHandler {

	private static $FATAL = array(
		E_USER_ERROR,
		E_RECOVERABLE_ERROR
	);

	private $htmlTemplate;

	public function __construct() {
		$this->htmlTemplate = $GLOBALS['HTML_UNCAUGHT_EXCEPTION'];
	}

	public function handleException($exception) {
		$isAsync = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
							strtolower($_SER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

		if ($exception instanceof ErrorException) {
			$title = 'Fatal Error';
		} else {
			$title = 'Uncaught ' . get_class($exception);
		}

		$stack = array();

		$e = $exception;
		while ($e !== null) {
			$stack[] = $this->getStack($e, $isAsync);
			$e = $e->getPrevious();
		}

		$stackSep = $this->formatStackSeparator($isAsync);
		$msg = implode($stackSep, $stack);

		if ($isAsync) {
			echo $msg;
			return;
		}

		echo StringUtils::format($this->htmlTemplate, $title, $msg);
	}

	public function handleError($errno, $errstr, $errfile, $errline) {
		if (!in_array($errno, self::$FATAL)) {
			// This error will not terminate script execution, continue with normal 
			// error handling
			return false;
		}

		throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	private function getStack(Exception $e, $isAsync) {
			$msg = $this->formatMessage($e->getMessage(), $isAsync);
			$msg.= $this->formatTrace($e->getTraceAsString(), $isAsync);

			if (!$isAsync) {
				$msg = "<div class=\"exception\">$msg</div>";
			}
			return $msg;
	}

	private function formatMessage($msg, $isAsync) {
		if ($isAsync) {
			return $msg;
		}

		return "<p class=\"exception-message\">$msg</p>";
	}

	private function formatTrace($trace, $isAsync) {
		if ($isAsync) {
			return "\n\n$trace";
		}

		return "<div class=\"exception-trace\"><pre>$trace</pre></div>";
	}

	private function formatStackSeparator($isAsync) {
		if ($isAsync) {
			return "\n\nCaused by:\n----------\n\n";
		}

		return "<div class=\"exception-stack-separator\">Caused by:</div>";
	}
}

/*
 * =============================================================================
 * HTML Output templates
 * =============================================================================
 */

// It may not be possible to rely on _p to generate a context aware path to 
// a CSS resources since that part of compilation may not yet have occured, so 
// instead the CSS for this page is written here
$HTML_UNCAUGHT_EXCEPTION_CSS = <<<CSS
html,body { height: 100%; }
body {
	margin: 0;
	padding: 0;
	background: -webkit-linear-gradient(top, #FFF, #DDD);
}
header {
	margin-bottom: 1em;
	padding: .25em;
	border-bottom: 1px solid #FFF;
	box-shadow: 0 1px 0 0 #333;

	font-family: 'Alegreya', serif;
	font-weight: 700;
	font-size: 2.5em;
	text-shadow: 1px 1px 0 #DDD;

	background-color: #BFBFBF;
	background: -webkit-linear-gradient(top, #BFBFBF, #DFDFDF);
}
.exception {
	margin: 0 6px;
}
.exception-message {
	margin: 0;
	padding: .5em 1em;

	font-family: 'Oxygen', sans-serif;
	font-size: 1.5em;
	color: #DDD;

	background-color: #A51409;
	background: -webkit-linear-gradient(left, #A51409, #A53429);
	border-bottom: 1px solid #FFF;
	border-right: 1px solid #FFF;
	box-shadow: 4px 4px 0 0 #333;
}
.exception-trace {
	margin: 5px 1em;
	padding: 1em;
	border-bottom: 1px solid #FFF;
	border-right: 1px solid #FFF;

	background-color: #555;
	background: -webkit-linear-gradient(top, #555, #444);
	box-shadow: 4px 4px 0 0 #333;
}
.exception-trace pre {
	margin: 0;
	padding-bottom: .5em;
	white-space: pre-wrap;

	font-family: 'Lekton', monospace;
	font-size: .9em;
	line-height: 2em;
	color: #DDD;
}
.exception-stack-separator {
	margin: 1em 6px 0;
	padding: 2em 1em .25em;
	border-top: 1px solid #AAA;

	font-family: 'Alegreya', serif;
	font-size: 1.25em;
	font-weight: 700;
}
CSS;

global $HTML_UNCAUGHT_EXCEPTION;
$HTML_UNCAUGHT_EXCEPTION = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>{0}</title>

	<link href='http://fonts.googleapis.com/css?family=Lekton|Oxygen|Alegreya:700' rel='stylesheet' type='text/css'>
	<style>
	$HTML_UNCAUGHT_EXCEPTION_CSS
	</style>
<body>
	<header>{0}</header>
	{1};
HTML;
