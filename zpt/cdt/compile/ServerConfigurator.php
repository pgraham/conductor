<?php
/**
 * Dynamically generated REST server configurator.  Configures a RestServer
 * instance to process all resources provided by this site.
 */
namespace zeptech\dynamic;

use \zeptech\rest\RestServer;
use \zpt\cdt\di\Injector;

class ServerConfigurator {

  public function configure(RestServer $server) {
    ${each:mappings as mapping}
      $hdlr = new \${mapping[hdlr]}(${join:mapping[args]:,});
      ${if:mapping[beans] ISSET}
        Injector::inject($hdlr, ${php:mapping[beans]});
      ${fi}
      ${each:mapping[uris] as uri}
        $server->addMapping('${uri}', $hdlr);
      ${done}

    ${done}

    ${each:actors as actor}
      $hdlr = ${actor[generator]}::get('${actor[definition]}');
      ${if:actor[beans] ISSET}
        Injector::inject($hdlr, ${php:actor[beans]});
      ${fi}
      ${each:actor[uris] as uri}
        $server->addMapping('${uri[template]}', $hdlr, '${uri[id]}', '${uri[method]}');
      ${done}
    ${done}
  }
}
