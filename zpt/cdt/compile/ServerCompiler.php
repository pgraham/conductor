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

use \zpt\cdt\di\DependencyParser;

/**
 * This class generates a RESTful ServerConfigurator implementation from a
 * set of mappings.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServerCompiler {

  private $_compressed;
  private $_mappings;
  private $_tmplParser;

  public function __construct($compressed = false) {
    $this->_compressed = false;
  }

  /**
   * Add a mapping to be compiled into the ServerConfigurator.
   *
   * @param string $hdlr Name of the class that will handle requests.
   * @param array $args List of arguments to be passed to the request handler's
   *   constructor.  NOTE: These cannot be object references, literals only!
   * @param array $uris List of URIs that are handled by the given handler.
   * @param array $beans List of bean id that should be injected into the
   *   handler.
   */
  public function addMapping($hdlr, $args, $uris) {
    $mapping = array(
      'hdlr' => $hdlr,
      'args' => $args,
      'uris' => $uris
    );

    $beans = DependencyParser::parse($hdlr);
    if ($beans !== null && count($beans) > 0) {
      $mapping['beans'] = $beans;
    }
    $this->_mappings[] = $mapping;
  }

  public function compile($pathInfo) {
    $resourceSrc = "$pathInfo[lib]/conductor/src/resources";
    $tmplSrc = "$resourceSrc/tmpl/ServerConfigurator.php";
    $tmplOut = "$pathInfo[target]/zeptech/dynamic/ServerConfigurator.php";

    $values = array( 'mappings' => $this->_mappings );
    $tmpl = $this->_tmplParser->parse(file_get_contents($tmplSrc));
    $tmpl->save($tmplOut, $values);
  }

  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }
}
