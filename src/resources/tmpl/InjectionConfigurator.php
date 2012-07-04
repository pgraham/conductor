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
      $bean = new \${bean[class]}();
      Injector::addBean('${bean[id]}', $bean);
      ${each:bean[props] as prop}
        $bean->set${prop[name]}(${php:prop[val]});
      ${done}

    ${done}

    // Inject bean dependencies
    ${each:beans as bean}
      ${if:bean[refs]}
        $bean = Injector::getBean('${bean[id]}');
        Injector::inject($bean, ${php: bean[refs]});

      ${fi}
    ${done}
  }
}
