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
use \Exception;

/**
 * Top level exception handler for sites that are in debug mode.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TopLevelDebugExceptionHandler {

	private $htmlTemplate;

	public function __construct() {
		$this->htmlTemplate = $GLOBALS['HTML_UNCAUGHT_EXCEPTION'];
	}

	public function handleException($exception) {
		$isAsync = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
							strtolower($_SER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

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

		echo StringUtils::format($this->htmlTemplate, $msg);
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

		return "<pre class=\"exception-trace\">$trace</pre>";
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
body {
	margin: 0;
	padding: 0;
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
	margin: 0 0 1.5em;
	padding: .25em 1em;

	font-family: 'Open Sans', sans-serif;
	font-size: 1.5em;

	background-color: #A51409;
	background: -webkit-linear-gradient(left, #A51409, #A53429);
	border-bottom: 1px solid #FFF;
	border-right: 1px solid #FFF;
	box-shadow: 4px 4px 0 0 #333;
}
.exception-trace {
	margin-left: 30px;
	width: 95%;
	padding: .5em .5em 1em;
	border: 1px dashed #AAA;
	overflow: auto;

	font-family: 'Droid Sans Mono', monospace;
	font-size: .8em;
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
	<title>Uncaught Exception</title>

	<link href='http://fonts.googleapis.com/css?family=Droid+Sans+Mono|Open+Sans|Alegreya:700' rel='stylesheet' type='text/css'>
	<style>
	$HTML_UNCAUGHT_EXCEPTION_CSS
	</style>
<body>
	<header>Uncaught Exception</header>
	{0};
HTML;
