<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile;

use \ArrayObject;
use \Exception;

/**
 * This class compiles a Configurator implementation for initiating the
 * conductor environment.
 *
 * In dev mode, this happens for every single request so this needs to happen
 * before almost anything else so that configuration complete for actual request
 * parsing, as well as for the other compilation steps.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConfigurationCompiler {

  private $_db;
  private $_env;
  private $_pathInfo;
  private $_namespace;

  private $_tmplParser;

  /**
   * Compile the site Configurator implementation for the site located at the
   * given root path.
   *
   * @param string $root The root path of site.
   */
  public function compile($root, $env) {
    $cfg = array();

    $xmlCfg = simplexml_load_file("$root/conductor.cfg.xml", 'SimpleXMLElement',
      LIBXML_NOCDATA);

    $this->_parsePathInfo($root, $xmlCfg);
    $this->_parseNamespace($xmlCfg);
    $this->_parseDb($xmlCfg);

    $logDir = null;
    if (isset($xmlCfg->logDir)) {
      $logDir = (string) $xmlCfg->logDir;
    }

    $tmplSrc = __DIR__ . '/Configurator.tmpl.php';
    $tmplOut = "{$this->_pathInfo['target']}/zeptech/dynamic/Configurator.php";

    $values = array(
      'pathInfo' => $this->_pathInfo->getArrayCopy(),
      'docRootLen' => strlen($this->_pathInfo['docRoot']),
      'webRootLen' => strlen($this->_pathInfo['webRoot']),
      'namespace' => $this->_namespace,
      'dbConfig' => $this->_db,
      'env' => $env,
      'logDir' => $logDir
    );

    $tmpl = $this->_tmplParser->parse(file_get_contents($tmplSrc));
    $tmpl->save($tmplOut, $values);
  }

  /**
   * Setter for the template parser.
   *
   * @param zpt\cdt\TemplateParser $parser The parser.
   */
  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }

  private function _parseDb($xmlCfg) {
    if (!isset($xmlCfg->db)) {
      throw new Exception('No database configuration found');
    }
    $dbConfig = array();

    if (!isset($xmlCfg->db->username)) {
      throw new Exception('No database username specified');
    }
    $dbConfig['db_user'] = (string) $xmlCfg->db->username;

    if (!isset($xmlCfg->db->password)) {
      throw new Exception('No database password specified');
    }
    $dbConfig['db_pass'] = (string) $xmlCfg->db->password;

    if (!isset($xmlCfg->db->schema)) {
      throw new Exception('No database schema specified');
    }
    $dbConfig['db_schema'] = (string) $xmlCfg->db->schema;

    $dbConfig['db_driver'] = (isset($xmlCfg->db->driver))
      ? (string) $xmlCfg->db->driver
      : 'mysql';

    $dbConfig['db_host'] = (isset($xmlCfg->db->host))
      ? (string) $xmlCfg->db->host
      : 'localhost';

    $this->_db = $dbConfig;
  }

  private function _parseNamespace($xmlCfg) {
    if (!isset($xmlCfg->namespace)) {
      throw new Exception("The site's namespace is not configured");
    }
    $this->_namespace = (string) $xmlCfg->namespace;
  }

  private function _parsePathInfo($root, $xmlCfg) {
    // Website config is found at the root of the website
    $webRoot = isset($xmlCfg->webRoot)
      ? (string) $xmlCfg->webRoot
      : '/';
    $docRoot = "$root/htdocs";
    $lib = "$root/lib";
    $src = "$root/src";
    $target = "$root/target";

    $pathInfo = new ArrayObject(array(
      'root' => $root,
      'webRoot' => $webRoot,
      'docRoot' => $docRoot,
      'lib' => $lib,
      'src' => $src,
      'target' => $target,
    ));

    $this->_pathInfo = $pathInfo;
  }
}
