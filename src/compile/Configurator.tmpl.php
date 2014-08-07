<?php

/**
 * Dynamically generated environment configuration provider.
 *
 * DO NOT EDIT.
 */
namespace /*# companionNs #*/ {

use zpt\cdt\config\RuntimeConfig;
use zpt\opal\Psr4Dir;
use ArrayObject;

class /*# companionClass #*/ implements RuntimeConfig {

  private $pathInfo;
  private $ns;
  private $env;
  private $dynTarget;

  public function __construct() {
    $this->pathInfo = new ArrayObject(/*# php:pathInfo #*/);
    $this->ns = /*# php:namespace #*/;
    $this->env = /*# php:env #*/;
    $this->dynTarget = new Psr4Dir(
      /*# php:dyn[target] #*/,
      /*# php:dyn[prefix] #*/
    );
  }

  public function getConfig() {

    return array(
      'pathInfo' => $this->pathInfo,
      'namespace' => $this->ns,
      'db_config' => /*# php:dbConfig #*/,
      'env' => $this->env,
      'logDir' => '/*# logDir #*/',
      'logLevel' => '/*# logLevel #*/',
      'dynTarget' => $this->dynTarget
    );
  }

  public function getPathInfo() {
    return $this->pathInfo;
  }

  public function getNamespace() {
    return $this->ns;
  }

  public function getEnvironment() {
    return $this->env;
  }

  public function getDynamicClassTarget() {
    return $this->dynTarget;
  }
}

} // End namespace \zpt\dyn

namespace { //global namespace
  // Function for transforming an absolute webpath into a context sensitive one.
  function _P($path) {
    #{if pathInfo[webRoot] = /
      return $path;
    #{else
      return '/*# pathInfo[webRoot] #*/' . $path;
    #}
  }

  // Function to transform a context sensitive path into an absolute path.
  function _AbsP($path) {
    #{if pathInfo[webRoot] = /
      return $path;
    #{else
      if (strpos($path, '/*# pathInfo[webRoot] #*/') === 0) {
        return substr($path, /*# webRootLen #*/);
      }
      return $path;
    #}
  }

  // Function to transform a file-system path into a web path
  function _fsToWeb($path) {
    if (substr($path, 0, /*# docRootLen #*/) === '/*# pathInfo[htdocs] #*/') {
      #{if pathInfo[webRoot] = /
        return substr($path, /*# docRootLen #*/);
      #{else
        return '/*# pathInfo[webRoot] #*/' . substr($path, /*# docRootLen #*/);
      #}
    }

    return false;
  }

  // Function to transform a context sensitive web path into a file system path.
  function _webToFs($path) {
    #{if pathInfo[webRoot] = /
      return '/*# pathInfo[htdocs] #*/' . $path;
    #{else
      return '/*# pathInfo[htdocs] #*/' . substr($path, /*# webRootLen #*/);
    #}
  }

}
