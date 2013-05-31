<?php
/**
 * Dynamically generated Injector configurator.  Configures the Injector
 * for all required objects.
 */
namespace zpt\dyn;

use \zpt\cdt\di\Injector;
use \zpt\orm\PdoWrapper;

class InjectionConfigurator {

  public function configure() {
    // Add the Clarinet PDOWrapper as a bean
    Injector::addBean('pdo', PdoWrapper::get());

    // Create beans and set scalar property values
    #{ each beans as bean
      $/*# bean[id] #*/ = new \/*# bean[class] #*/(/*# join-php:bean[ctor]:, #*/);
      Injector::addBean('/*# bean[id] #*/', $/*# bean[id] #*/);
    #}

    // Inject bean dependencies
    #{ each beans as bean
      #{ if bean[props]
        #{ each bean[props] as prop
          #{ if prop[ref] ISSET
            $/*# bean[id] #*/->set/*# prop[name] #*/(Injector::getBean('/*# prop[ref] #*/'));
          #{ elseif prop[val] ISSET
            $/*# bean[id] #*/->set/*# prop[name] #*/(/*# php:prop[val] #*/);
          #{ elseif prop[type] ISSET
            $/*# bean[id] #*/->set/*# prop[name] #*/(Injector::getBeans('/*# prop[type] #*/'));
          #}
        #}
      #}
    #}

    #{ each beans as bean
      #{ if bean[init]
        $/*# bean[id] #*/->/*# bean[init] #*/();
      #}
    #}
  }
}
