<?php
/**
 * This is a PHP code template for configuring dependency injection.
 *
 * This is generated file - DO NOT EDIT
 *
 * TODO Add support for constructor args injection
 */
use \zeptech\orm\runtime\PdoWrapper;
use \zpt\cdt\di\Injector;

// Add the Clarinet PDOWrapper as a bean
Injector::addBean('pdo', PdoWrapper::get());

// Create beans and set scalar property values
${each:beans as bean}
  $bean = new \${bean[class]}();
  ${each:bean[props] as prop}
    $bean->set${prop[name]}(${php:prop[val]});
  ${done}
  Injector::addBean('${bean[id]}', $bean);

${done}

// Inject bean dependencies
${each:beans as bean}
  $bean = Injector::getBean('${bean[id]}');
  ${each:bean[refs] as ref}
    $bean->set${ref[name]}(Injector::getBean('${ref[ref]}'));
  ${done}

${done}
