<?php
/**
 * This is a generated class - DO NOT EDIT.
 */
namespace /*# companionNs #*/;

use Psr\Log\LoggerAwareInterface;
use zpt\cdt\di\InjectedLoggerAwareTrait;
use zpt\cdt\di\Injector;
use zpt\cdt\rest\BeanRequestHandler;
use zpt\rest\BaseRequestHandler;
use zpt\rest\RequestHandler;
use zpt\rest\Request;
use zpt\rest\Response;
use Exception;

class /*# companionClass #*/ extends BaseRequestHandler
  implements BeanRequestHandler, LoggerAwareInterface {

  use InjectedLoggerAwareTrait;

  private $service;
  private $session;
  private $mappings;
  private $pdo;

  public function __construct() {
    $this->mappings = /*# php:mappings #*/;
  }

  public function getMappings() {
    return $this->mappings;
  }

  public function setService(\/*# model #*/ $service) {
    $this->service = $service;
  }

  public function setSession($session) {
    $this->session = $session;
  }

  public function setPdo($pdo) {
    $this->pdo = $pdo;
  }

  #{ each methodTypes as methodType
    public function /*# methodType[type] #*/(Request $request, Response $response) {
      try {
        $this->pdo->beginTransaction();

        $mappingId = $request->getMappingId();
        $uri = $request->getUri();
        $this->logger->debug(
          "[DISPATCH] Mapping $uri ($mappingId) to /*# model #*/ service
        ");
        switch ($mappingId) {

          #{ each methodType[methods] as method
            case '/*# method[name] #*/':
            #{ if method[enforceOrder]
              if ($request->hasData('__ROT')) {
                $rot = $request->getData('__ROT');
                $uriHash = 'rot-' . $request->getUri();
                $curRot = $this->session->get($uriHash);
                if ($curRot === null || $curRot < $rot) {
                  $this->session->set($uriHash, $rot);
                } else {
                  $response->setData(array(
                    'success' => null
                  ));
                }
              }

            #}
            $this->service->/*# method[name] #*/($request, $response);
            $this->pdo->commit();
            return;

          #}
        }
        parent::/*# methodType[type] #*/($request, $response);
      } catch (Exception $e) {
        $this->pdo->rollback();
        throw $e;
      }
    }
  #}
}
