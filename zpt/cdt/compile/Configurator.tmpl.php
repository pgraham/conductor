<?php
/**
 * Dynamically generated environment configuration provider.
 *
 * DO NOT EDIT.
 */
namespace zpt\dyn {

use \ArrayObject;

class Configurator {

  public static function getConfig() {
    $pathInfo = new ArrayObject(${php:pathInfo});

    return array(
      'pathInfo' => $pathInfo,
      'namespace' => ${php:namespace},
      'db_config' => ${php:dbConfig},
      'env' => ${php:env},
      'logDir' => '${logDir}'
    );
  }
}

} // End namespace \zpt\dyn

namespace { //global namespace
  // Function for transforming an absolute webpath into a context sensitive one.
  function _P($path) {
    ${if:pathInfo[webRoot] = /}
      return $path;
    ${else}
      return '${pathInfo[webRoot]}' . $path;
    ${fi}
  }

  // Function to transform a context sensitive path into an absolute path.
  function _AbsP($path) {
    ${if:pathInfo[webRoot] = /}
      return $path;
    ${else}
      if (strpos($path, '${pathInfo[webRoot]}') === 0) {
        return substr($path, ${webRootLen});
      }
      return $path;
    ${fi}
  }

  // Function to transform a file-system path into a web path
  function _fsToWeb($path) {
    if (substr($path, 0, ${docRootLen}) === '${pathInfo[docRoot]}') {
      ${if:pathInfo[webRoot] = /}
        return substr($path, ${docRootLen});
      ${else}
        return '${pathInfo[webRoot]}' . substr($path, ${docRootLen});
      ${fi}
    }

    return false;
  }

  // Function to transform a context sensitive web path into a file system path.
  function _webToFs($path) {
    ${if:pathInfo[webRoot] = /}
      return '${pathInfo[docRoot]}' . $path;
    ${else}
      return '${pathInfo[docRoot]}' . substr($path, ${webRootLen});
    ${fi}
  }

}
