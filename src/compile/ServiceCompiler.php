<?php
/**
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile;

use \zpt\anno\Annotations;
use \zpt\cdt\di\Injector;
use \zpt\cdt\rest\ServiceDispatcherCompanionDirector;
use \zpt\opal\CompanionGenerator;
use \zpt\util\File;
use \DirectoryIterator;
use \ReflectionClass;
use \StdClass;

/**
 * This class compiles service definition classes.  There are two ways of
 * defining a service.  Either by directly implementing a the
 * {@link zpt\rest\RequestHandler} interface, or by annotating a class with
 * @Service and annotating the class' methods with @Method {DELETE|GET|POST|PUT} * and @Uri <uri>.
 *
 * In the latter case, a {@link zpt\rest\RequestHandler} interface will be
 * generated which dispatches requests for the specified URIs to the appropriate
 * method.
 *
 * In both cases, a mapping for the service will added to the
 * {@link zpt\rest\RestServer} instance handling the request by the
 * {@link zpt\rest\ServerConfigurator} generated by the compilation process.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ServiceCompiler {

  /*
   * The dependency injection compiler for adding bean definitions for
   * compiled services.
   */
  private $_diCompiler;

  /*
   * The server compiler that generates a ServerConfigurator for adding mappings
   * to a RestServer instance.
   */
  private $_serverCompiler;

  /* Service request dispatcher generator. */
  private $srvcDispatcherGen;

  /**
   * Compile the services in the given directory.
   *
   * @param string $srvcs Path to the directory that contains the services to
   *   compile
   * @param string $ns The namespace of the services in the given directory.
   */
  public function compile($srvcs, $ns) {
    if (!file_exists($srvcs)) {
      // Nothing to do here
      return;
    }

    $dir = new DirectoryIterator($srvcs);
    foreach ($dir as $srvc) {
      $fname = $srvc->getBasename();

      // Don't process dot files or hidden files or directories
      if ($srvc->isDot() || substr($fname, 0, 1) === '.') {
        continue;
      }

      // Recurse into subdirectories
      if ($srvc->isDir()) {
        $this->compile($srvc->getPathname(), "$ns\\$fname");
        continue;
      }

      // Order is important.  This is not a directory so make sure it is a PHP
      // file before continuing
      if (!File::checkExtension($fname, 'php')) {
        continue;
      }

      $srvcName = $srvc->getBasename('.php');
      $srvcClass = "$ns\\$srvcName";

      $this->compileService($srvcClass);
    }
  }

  /**
   * Compile the given service class.  Currently there are two supported ways
   * of defining a compiled service class.  The first is to directly implement
   * the RequestHandler interface and the second is to add a @Service annotation
   * to the class.  For these classes a ServiceRequestDispatcher will be
   * generated and used to bridge between the RestServer and Service instance.
   *
   *
   * @param string $srvcClass The name of the service class.
   * @param string $srvcBeanId Used only for serviced declared using @Service
   *   annotations.  If given, then instead of adding an instance of the
   *   service to DI, the specified bean will be used as the service instance
   *   of the generated ServiceRequestDispatcher
   */
  public function compileService($srvcClass, $srvcBeanId = false) {
    $srvcDef = new ReflectionClass($srvcClass);
    $annos = new Annotations($srvcDef);

    // There are two methods of defining a service.
    if ($srvcDef->implementsInterface('zpt\rest\RequestHandler')) {
      // Direct RequestHandler implementation
      if (isset($annos['uri'])) {

        $uris = $annos->asArray('uri');
        $mappings = array();
        foreach ($uris as $uri) {
          $mapping = new StdClass();
          $mapping->uri = $uri;

          $mappings[] = $mapping;
        }


        $beanId = str_replace('\\', '_', $srvcClass);
        $props = array(
          array(
            'name' => 'Mappings',
            'val'  => $mappings
          )
        );
        $this->_diCompiler->addBean($beanId, $srvcClass, $props);
        $this->_serverCompiler->addBean($beanId);
      } else {
        // TODO Request handler that declares no URIs, generate warning
      }
    } else if (isset($annos['service'])) {
      // Service definition, generate a RequestHandler implementation for this
      // class that handles delegation to the appropriate method of the
      // service.

      // Generate a service handler for this class
      $genClass = $this->srvcDispatcherGen->generate($srvcClass);

      // Add a DI bean for the actual service
      if (!$srvcBeanId) {
        $srvcBeanId = Injector::generateBeanId($srvcClass);
        $this->_diCompiler->addBean($srvcBeanId, $srvcClass);
      }

      // Add a DI bean for the request dispatcher.  The generated dispatcher
      // will be injected with the service instance using annotation
      // configuration
      $dispatcherBeanId = Injector::generateBeanId($srvcClass,
        ServiceDispatcherCompanionDirector::BEAN_ID_SUFFIX);
      $this->_diCompiler->addBean(
        $dispatcherBeanId,
        $genClass,
        array(
          array(
            'name' => 'service',
            'ref' => $srvcBeanId
          ),
          array(
            'name' => 'pdo',
            'ref' => 'pdo'
          ),
          array(
            'name' => 'session',
            'ref' => 'session'
          )
        )
      );

      // Add the dispatcher bean as a RequestHandler
      $this->_serverCompiler->addBean($dispatcherBeanId);

    } /* Else: ignore this class, it is not a service definition. */
  }

  /**
   * Setter for the DependencyInjectionCompiler.
   *
   * @param DependencyInjectionCompiler $diCompiler
   */
  public function setDependencyInjectionCompiler($diCompiler) {
    $this->_diCompiler = $diCompiler;
  }

  /**
   * Setter for the Server compiler which generates a ServerConfigurator for
   * adding mappings to a {@link \zpt\rest\RestServer} instance.
   *
   * @param ServerCompiler $serverCompiler The compiler instance.
   */
  public function setServerCompiler($serverCompiler) {
    $this->_serverCompiler = $serverCompiler;
  }

  /**
   * Setter for the service request dispatcher used to generate
   * {@link \zpt\rest\RequestHandler} implementation that dispatch requests
   * to service classes.
   *
   * @param \zpt\cdt\rest\ServiceRequestDispatcher $serviceRequestDispatcher The
   *   generator instance.
   */
  public function setServiceDispatcherGenerator(CompanionGenerator $generator) {
    $this->srvcDispatcherGen = $generator;
  }
}
