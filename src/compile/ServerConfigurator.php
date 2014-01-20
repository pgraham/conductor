<?php
/**
 * Dynamically generated REST server configurator.  Configures a RestServer
 * instance to process all resources provided by this site.
 */
namespace zpt\dyn;

use \zpt\cdt\di\Injector;
use \zpt\cdt\rest\InjectedRestServer;

class ServerConfigurator {

  public function configure(InjectedRestServer $server) {
    #{ each beans as beanId
      $hdlr = Injector::getBean('/*# beanId #*/');
      $server->addBeanRequestHandler($hdlr);
    #}

    #{ each mappings as mapping
      $hdlr = new \/*# mapping[hdlr] #*/(/*# join:mapping[args]:, #*/);
      #{ if mapping[beans] ISSET
        Injector::inject($hdlr, /*# php:mapping[beans] #*/);
      #}
      #{ each mapping[uris] as uri
        $server->addMapping('/*# uri #*/', $hdlr);
      #}

    #}
  }
}
