<?php
/**
 * Dynamically generated Injector configurator.  Configures the Injector
 * for all required objects.
 */
namespace /*# namespace #*/;

use \zpt\cdt\di\Injector;

class InjectionConfigurator {

  public function configure() {

    // Create beans and set scalar property values
    #{ each beans as bean
      $/*# bean[id] #*/ = new \/*# bean[class] #*/(/*# join:bean[ctor]:, #*/);
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
