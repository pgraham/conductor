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

use \zeptech\orm\generator\PersisterGenerator;
use \zeptech\orm\generator\TransformerGenerator;
use \zeptech\orm\generator\ValidatorGenerator;
use \zeptech\orm\QueryBuilder;
use \zpt\cdt\di\DependencyParser;
use \zpt\cdt\i18n\ModelMessages;

/**
 * This class compiles a script which initializes the dependency injection
 * container.  Beans which are inserted into the container are parsed from
 * XML files defined by conductor and my any installed modules.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyInjectionCompiler {

  private $_files = array();
  private $_beans = array();

  private $_tmplParser;

  public function addBean($id, $class, $props = array()) {
    $bean = DependencyParser::parse($id, $class);

    // Merge annotation configured beans with spefied bean property values.
    // Specified property values override annotation configuration.
    $bean['props'] = array_merge(
      $bean['props'],
      $props
    );

    $this->_beans[] = $bean;
  }

  public function addFile($file) {
    if (file_exists($file)) {
      $this->_files[] = $file;
    }
  }

  public function compile($pathInfo, $ns) {
    foreach ($this->_files as $context) {
      if (file_exists($context)) {
        $cfg = simplexml_load_file($context, 'SimpleXMLElement',
          LIBXML_NOCDATA);

        foreach ($cfg->bean as $beanDef) {
          $bean['id'] = $beanDef['id'];
          $bean['class'] = $beanDef['class'];

          // Parse any annotation configuration or marker interfaces before
          // applying XML configuration
          $bean = DependencyParser::parse(
            (string) $beanDef['id'],
            (string) $beanDef['class']
          );

          if (isset($beanDef['initMethod'])) {
            $bean['init'] = (string) $beanDef['initMethod'];
          }

          $props = array();
          if (isset($beanDef->property)) {
            $propDefs = $beanDef->property;

            foreach ($propDefs as $propDef) {
              $prop = array();
              $prop['name'] = (string) $propDef['name'];

              if (isset($propDef['value'])) {
                $prop['val'] = $this->getScalar((string) $propDef['value']);
              } else if (isset($propDef['ref'])) {
                $prop['ref'] = (string) $propDef['ref'];
              } else if (isset($propDef['type'])) {
                $prop['type'] = (string) $propDef['type'];
              } else {
                // TODO Warn about an invalid bean definition
              }
              $props[] = $prop;
            }
          }
          $bean['props'] = array_merge($bean['props'], $props);

          $ctorArgs = array();
          if (isset($beanDef->ctorArg)) {
            $ctor = $beanDef->ctorArg;

            // TODO Is order guaranteed by XML parser?
            foreach ($ctor as $arg) {
              if (isset($arg['value'])) {
                $ctorArgs[] = $this->getScalar((string) $arg['value']);
              } else if (isset($arg['ref'])) {
                $ctorArgs[] = '$' . ((string) $arg['ref']);
              } else {
                // TODO Warn about an invalid bean definition
              }
            }
          }
          $bean['ctor'] = $ctorArgs;

          $this->_beans[] = $bean;
        }
      }
    }

    // Build the InjectionConfiguration script
    $srcPath = __DIR__ . '/InjectionConfigurator.php';
    $outPath = "$pathInfo[target]/zpt/dyn/InjectionConfigurator.php";
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));

    $values = array(
      'persisterNs' => PersisterGenerator::$actorNamespace,
      'validatorNs' => ValidatorGenerator::$actorNamespace,
      'transformerNs' => TransformerGenerator::$actorNamespace,
      'queryBuilderNs' => QueryBuilder::$actorNamespace,
      'messagesNs' => ModelMessages::$actorNamespace,
      'beans' => $this->_beans
    );
    $tmpl->save($outPath, $values);
  }

  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }

  private function getScalar($val)
  {
      if (is_numeric($val)) {
          return (float) $val;
      } else if (strtolower($val) === 'true') {
          return true;
      } else if (strtolower($val) === 'false') {
          return false;
      } else if (strtolower($val) === 'null') {
          return null;
      }
      return $val;
  }
}
