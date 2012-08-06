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
namespace zpt\cdt\compile;

use \reed\Markdown;
use \SplFileInfo;

/**
 * Compiler for language files.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class L10NCompiler {

  private $_compressed = false;
  private $_languages = array();
  private $_tmplParser;

  public function __construct($compressed) {
    $this->_compressed = $compressed;
  }

  public function addLanguageFile(SplFileInfo $file) {
    $strings = $this->_parseStrings(file_get_contents($file->getPathname()));
    $this->addStrings($file->getBasename('.messages'), $strings);
  }

  public function addStrings($lang, array $strings) {
    $this->_ensureLang($lang);

    foreach ($strings as $key => $value) {
      $this->_languages[$lang][$key] = $value;
    }
  }

  public function compile($pathInfo) {
    $phpTmplSrc = __DIR__ . "/language.strings.tmpl.php";
    $phpTmpl = $this->_tmplParser->parse(file_get_contents($phpTmplSrc));

    $jsTmplSrc = __DIR__ . '/language.strings.tmpl.js';
    $jsTmpl = $this->_tmplParser->parse(file_get_contents($jsTmplSrc));

    foreach ($this->_languages as $lang => $strings) {
      $values = array('strings' => $strings);

      $phpOutPath = "$pathInfo[target]/i18n/$lang.strings.php";
      $phpTmpl->save($phpOutPath, $values);

      $jsOutPath = "$pathInfo[target]/htdocs/js/$lang.js";
      $jsTmpl->save($jsOutPath, $values);
    }
  }

  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }

  private function _ensureLang($lang) {
    if (!isset($this->_languages[$lang])) {
      $this->_languages[$lang] = array();
    }
  }

  private function _parseStrings($msgs) {
    $result = array();

    $lines = explode("\n", $msgs);
    $key = null;
    $val = array();

    $isWaitingForOtherLine = false;
    foreach ($lines as $line) {
      $line = trim($line);

      if (empty($line) || ($key === null && strpos($line, '#') === 0)) {
        continue;
      }

      if ($key === null) {
        $eqPos = strpos($line, '=');
        $key = substr($line, 0, $eqPos);
        $value = substr($line, $eqPos + 1);

      } else {
        $value = $line;
      }

      // Check if ends with single '\'
      if (substr($value, -1) !== '\\') {
        $val[] = $value;

        $raw = implode(' ', $val);
        $md = Markdown::parseInline($raw);
        $result[$key] = array(
          'md' => $md,
          'raw' => $raw
        );

        $key = null;
        $val = array();
      } else {
        $val[] = substr($value, 0, -1);
      }
    }

    return $result;
  }
}
