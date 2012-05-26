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
      $hdlr = new ${mapping[hdlr]}(${join:mapping[hdlrArgs]:,});
      ${if:mapping[dependencies] ISSET}
        Injector::inject($hdlr, ${php:mapping[dependencies]});
      ${fi}

      ${each:mapping[tmpls] as tmpl}
        $server->addMapping('${tmpl}', $hdlr);
      ${done}

    ${done}
  }
}
