<?php
/**
 * Dynamically generated environment configuration provider.
 *
 * DO NOT EDIT.
 */
namespace zeptech\dynamic {

use \ArrayObject;

class Configurator {

  public static function getConfig() {
    $pathInfo = new ArrayObject(${php:pathInfo});

    return array(
      'pathInfo' => $pathInfo,
      'namespace' => ${php:namespace},
      'db_config' => ${php:dbConfig},
      'env' => ${php:env}
    );
  }
}

} // End namespace \zeptech\dynamic

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
}
