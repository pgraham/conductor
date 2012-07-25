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

  public function addStrings($lang, array $strings) {
    $this->_ensureLang($lang);

    foreach ($strings as $key => $value) {
      $this->_languages[$lang][$key] = $value;
    }
  }

  public function compile($pathInfo) {
    $tmplSrcPath = __DIR__ . "/language.strings.tmpl.php";
    $tmpl = $this->_tmplParser->parse(file_get_contents($tmplSrcPath));

    foreach ($this->_languages as $lang => $strings) {
      $outPath = "$pathInfo[target]/i18n/$lang.strings.php";
      $tmpl->save($outPath, array('strings' => $strings));
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
}
