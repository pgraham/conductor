<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace conductor\rest;

/**
 * This class encapsulates a the process of handling a RESTful request for a
 * resource.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class RestServer {

  /*
   * The declared formats for the response which will be recognized by the
   * requesting agent.
   */
  private $_acceptTypes = array('application/json');

  /*
   * Any data send with the request.  This will only be passed to a handler when
   * processing a POST or PUT request.
   */
  private $_data;

  /*
   * Map of URI's and their handlers that are recognized by this server.
   */
  private $_mappings = array();

  /*
   * Any query parameters passed with the request.  These will only be passed
   * to a handler when processing a GET request.
   */
  private $_query;


  /*
   * Object which encapsulates the request being handled by the server.
   * This will not be populated until {@link #handleRequest()} is called.
   */
  private $_request;

  /*
   * Object which encapsulates the response to send to the requesting agent.
   * This will not be populated until {@link #handleRequest()} is called.
   */
  private $_response;

  /**
   * Add a resource mapping for a resource URI handled by this server.  More
   * specific mappings need to be added first.
   *
   * @param string $uriTemplate URI template for URI to associate to the given
   *   ResourceRequestHandler
   * @param ResourceRequestHandler $handler
   */
  public function addMapping($uriTemplate, ResourceRequestHandler $handler) {
    $this->_mappings[] = array(
      'template' => $uriTemplate,
      'handler' => $handler
    );
  }

  /**
   * Getter for the body text of the response.
   *
   * @return string
   */
  public function getResponse() {
    if ($this->_response === null) {
      return '';
    }

    // For each of accepted types specified in the request, attempt to find
    // an appropriate encoder.
    $encoder = null;
    foreach ($this->_acceptTypes AS $acceptType) {
      $encoder = EncoderFactory::getEncoder($acceptType);
      if ($encoder !== null) {
        break;
      }
    }

    
    if ($encoder === null) {
      // No supported type was found. According the HTTP/1.1 spec a
      // '406 Not Acceptable' header can be returned or:
      //
      //   "... HTTP/1.1 servers are allowed to return responses which are
      //    not acceptable according to the accept headers sent in the
      //    request. In some cases, this may even be preferable to sending a
      //    406 response. User agents are encouraged to inspect the headers of
      //    an incoming response to determine if it is acceptable."
      //
      // So instead of returning a 406 Not Acceptable, a TextEncoder is used to
      // return a text/plain response
      $encoder = new TextEncoder();
    }
    return $encoder->encode($this->_response);
  }

  /**
   * Getter for any headers to send with the response.
   *
   * @return array
   */
  public function getResponseHeaders() {
    if ($this->_response === null) {
      return array();
    }
    return $this->_response->getHeaders();
  }

  /**
   * Handle a resource request.
   *
   * @param string $action The requested action to perform on the resource
   *   specified by the given URI.
   * @param string $uri
   */
  public function handleRequest($action, $uri) {
    $handler = null;
    foreach ($this->_mappings AS $mapping) {
      $preg = '/^' . preg_quote($mapping['template'], '/') . '/';
      if (preg_match($preg, $uri)) {
        $handler = $mapping['handler'];
        break;
      }
    }

    $this->_response = new ResourceResponse();
    if ($handler === null) {
      $this->_response->header('HTTP/1.1 404 Not Found');
      return;
    }

    $this->_request = new ResourceRequest();
    switch (strtoupper($action)) {
      case 'DELETE':
      $handler->delete($this->_request, $this->_response);
      break;

      case 'GET':
      $this->_request->setQuery($this->_query);
      $handler->get($this->_request, $this->_response);
      break;

      case 'POST':
      $this->_request->setData($this->_data);
      $handler->post($this->_request, $this->_response);
      break;

      case 'PUT':
      $this->_request->setData($this->_data);
      $handler->put($this->_request, $this->_response);
      break;
    }
  }

  /**
   * Set the response formats accepted by the requesting agents.
   *
   * @param string $accept HTTP Accept header
   */
  public function setAcceptType($accept) {
    // HTTP/1.1 Accept Header Definition:
    // ----------------------------------
    //
    // Accept         = "Accept" ":"
    //                  #( media-range [ accept-params ] )
    //
    // media-range    = ( "*/*"
    //                  | ( type "/" "*" )
    //                  | ( type "/" subtype )
    //                  ) *( ";" parameter )
    // accept-params  = ";" "q" "=" qvalue *( accept-extension )
    // accept-extension = ";" token [ "=" ( token | quoted-string ) ]

    // $this->_acceptTypes = explode(', ', $accept);

    if (preg_match_all('/(\*|[^\/,;=\s]+)\/(\*|[^\/,;=\s]+)(?:;\s*q\s*=\s*(1|0\.\d+))?,?/',
        $accept, $matches, PREG_SET_ORDER))
    {
      $this->_acceptTypes = array();
      foreach ($matches AS $match) {
        $accept = new AcceptType($match[1], $match[2]);
        if (isset($match[3])) {
          $accept->setQValue((float) $match[3]);
        }

        $this->_acceptTypes[] = $accept;
      }

      usort($this->_acceptTypes, function ($a, $b) {
        $aq = $a->getQValue();
        $bq = $b->getQValue();

        // If q-values are equal, preserve their order
        if ($aq == $bq) {
          return 1;
        }

        // We are sorting by descending q-value
        if ($aq > $bq) {
          return -1;
        } else {
          return 1;
        }
      });

    }
  }

  /**
   * Setter for any data passed with the request.  Valid only for POST and PUT
   * requests.
   *
   * @param array $data
   */
  public function setData(array $data) {
    $this->_data = $data;
  }

  /**
   * Setter for any query parameters passed with the request.  Valid only for
   * GET requests.
   *
   * @param array $query
   */
  public function setQuery(array $query) {
    $this->_query = $query;
  }

}
