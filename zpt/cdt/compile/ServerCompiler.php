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
use \zpt\cdt\di\Injector;

/**
 * This class generates a RESTful ServerConfigurator implementation from a
 * set of mappings.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServerCompiler {

  /* List of generated request handlers */
  private $_actors = array();

  /* List of request handlers */
  private $_mappings = array();

  /* List of bean RequestHandler implementations. */
  private $_beans = array();

  private $_tmplParser;

  public function addActor($generator, $def, $uris) {
    $this->_actors[] = array(
      'generator' => $generator,
      'definition' => $def,
      'uris' => $uris
    );
  }

  public function addBean($beanId) {
    $this->_beans[] = $beanId;
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

    $beans = DependencyParser::parse('', $hdlr);
    if ($beans['props'] !== null && count($beans['props']) > 0) {
      $mapping['beans'] = $beans['props'];
    }
    $this->_mappings[] = $mapping;
  }

  public function compile($pathInfo) {
    $tmplSrc = __DIR__ . '/ServerConfigurator.php';
    $tmplOut = "$pathInfo[target]/zpt/dyn/ServerConfigurator.php";

    $values = array(
      'mappings' => $this->_mappings,
      'actors' => $this->_actors,
      'beans' => $this->_beans
    );
    $tmpl = $this->_tmplParser->parse(file_get_contents($tmplSrc));
    $tmpl->save($tmplOut, $values);
  }

  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }
}
