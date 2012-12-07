<?php
/**
 * Dynamically generated Injector configurator.  Configures the Injector
 * for all required objects.
 */
namespace zeptech\dynamic;

use \zeptech\orm\runtime\PdoWrapper;
use \zpt\cdt\di\Injector;

class InjectionConfigurator {

  public function configure() {
    // Add the Clarinet PDOWrapper as a bean
    Injector::addBean('pdo', PdoWrapper::get());

    // Create beans and set scalar property values
    ${each:beans as bean}
      Injector::addBean('${bean[id]}', new \${bean[class]}());
    ${done}

    // Inject bean dependencies
    ${each:beans as bean}
      ${if:bean[props]}
        $bean = Injector::getBean('${bean[id]}');
        ${each:bean[props] as prop}
          ${if:prop[ref] ISSET}
            $bean->set${prop[name]}(Injector::getBean('${prop[ref]}'));
          ${elseif:prop[val] ISSET}
            $bean->set${prop[name]}(${php:prop[val]});
          ${elseif:prop[type] ISSET}
            $bean->set${prop[name]}(Injector::getBeans('${prop[type]}'));
          ${fi}
        ${done}

      ${fi}
    ${done}
  }
}
