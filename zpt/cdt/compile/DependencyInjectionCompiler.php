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
 * This class compiles a script which initializes the dependency injection
 * container.  Beans which are inserted into the container are parsed from
 * XML files defined by conductor and my any installed modules.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DependencyInjectionCompiler {

  private $_compressed;

  private $_files = array();
  private $_beans = array();

  private $_tmplParser;

  public function __construct($compressed) {
    $this->_compressed = $compressed;
  }

  public function addBean($id, $class, $refs) {
    $this->_beans[] = array(
      'id' => $id,
      'class' => $class,
      'refs' => $refs,
      'props' => array()
    );
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
          $bean = array();
          $bean['id'] = $beanDef['id'];
          $bean['class'] = $beanDef['class'];

          $props = array();
          $refs = array();
          if (isset($beanDef->property)) {
            $propDefs = $beanDef->property;
            if (!is_array($propDefs)) {
              $propDefs = array($propDefs);
            }

            foreach ($propDefs as $propDef) {
              $prop = array();

              if (isset($propDef['value'])) {
                $prop['name'] = $propDef['name'];
                $val = $propDef['value'];
                if (is_numeric($val)) {
                  $val = (float) $val;
                } else if (strtolower($val) === 'true') {
                  $val = true;
                } else if (strtolower($val) === 'false') {
                  $val = false;
                }
                $prop['val'] = $val;
                
                $props[] = $prop;
              } else if (isset($propDef['ref'])) {
                $prop['id'] = $propDef['name'];
                $prop['lookup'] = 'byId';
                $refs[] = $prop;
              } else {
                // TODO Warn about an invalid bean definition
              }
            }
          }
          $bean['props'] = $props;
          $bean['refs'] = $refs;

          $this->_beans[] = $bean;
        }
      }
    }

    // Build the InjectionConfiguration script
    $srcPath = "$pathInfo[lib]/conductor/src/resources/tmpl/InjectionConfigurator.php";
    $outPath = "$pathInfo[target]/zeptech/dynamic/InjectionConfigurator.php";
    $tmpl = $this->_tmplParser->parse(file_get_contents($srcPath));
    $tmpl->save($outPath, array('beans' => $this->_beans));
  }

  public function setTemplateParser($templateParser) {
    $this->_tmplParser = $templateParser;
  }
}
