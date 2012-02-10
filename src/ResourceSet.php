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
namespace conductor;

use \conductor\compile\Compilable;
use \conductor\jslib\Library;
use \oboe\Element;
use \reed\WebSitePathInfo;
use \RuntimeException;

/**
 * This class represents a list of related resources.  All resources in a
 * resource list must be relative to a specified base path.  The base can be
 * '/'.  The exception to this are external resources which reside on another
 * server.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceSet {

  private $_css = array();
  private $_fonts = array();
  private $_imgs = array();
  private $_js = array();
  private $_libs = array();
  private $_srvcs = array();

  /**
   * Create a new resource set for resource which live under the given source
   * path and will be made available under the given web-accessible path.
   */
  public function __construct() {
  }

  /**
   * Add a list of fonts to the resource set.
   * TODO Create an object to encapsulate font information.
   *
   * @param array $fonts
   */
  public function addFonts(array $fonts) {
    foreach ($fonts AS $font) {
      $this->addFont($font);
    }
  }

  /**
   * Add a font to the resource set.
   * TODO Create an object to encapsulate font information.
   *
   * @param string $font Font description recognized by Google Web Fonts API.
   */
  public function addFont($font) {
    $this->_fonts[] = $font;
  }

  /**
   * Add a set of images to the resource set.
   *
   * @param array $imgs List of paths to the images to include.
   */
  public function addImages(array $imgs) {
    $this->_imgs = array_merge($this->_imgs, $imgs);
  }

  /**
   * Add an image to the resource set.
   *
   * @param string $img Path to the image to include.
   */
  public function addImage($img) {
    $this->_imgs[] = $img;
  }

  /**
   * Add a set of Javascript Libraries to the resource set.
   *
   * @param Library[] $jslibs
   */
  public function addJsLibs(array $jslibs) {
    foreach ($jslibs AS $jslib) {
      $this->addJsLib($jslib);
    }
  }

  /**
   * Add a Javascript Library to the resource set.
   *
   * @param Library $jslib
   */
  public function addJsLib(Library $jslib) {
    $this->_libs[] = $jslib;
  }

  /**
   * Add a set of scripts to the resource set.
   *
   * @param array $scripts
   */
  public function addScripts(array $scripts) {
    $this->_js = array_merge($this->_js, $scripts);
  }

  /**
   * Add a script to the resource set.
   *
   * @param string $script
   */
  public function addScript($script) {
    $this->_js[] = $script;
  }

  /**
   * Add a set of services to the resource set.
   *
   * @param array $srvcs
   */
  public function addServices(array $srvcs) {
    $this->_srvcs = array_merge($this->_srvcs, $srvcs);
  }

  /**
   * Add a service to the resource set.
   *
   * @param string $srvc The name of the service definition class.
   */
  public function addService($srvc) {
    $this->_srvcs[] = $srvc;
  }

  /**
   * Add a set of stylesheets to the resource set.
   *
   * @param array $sheets
   */
  public function addSheets(array $sheets) {
    $this->_css = array_merge($this->_css, $sheets);
  }

  /**
   * Add a stylesheet to the resource set.
   *
   * @param string $sheet
   */
  public function addSheet($sheet) {
    $this->_css[] = $sheet;
  }

  public function getFonts() {
    return $this->_fonts;
  }

  public function getImages() {
    return $this->_imgs;
  }

  public function getJsLibs() {
    return $this->_libs;
  }

  public function getScripts() {
    return $this->_js;
  }

  public function getServices() {
    return $this->_srvcs;
  }

  public function getSheets() {
    return $this->_css;
  }

  /**
   * Included the resources in the set in the page response.
   *
   * @param WebSitePathInfo $pathInfo Path information for the site in which the
   *   resources are being included.
   * @param boolean $devMode Whether or not the site is in dev mode.
   */
  public function inc(WebSitePathInfo $pathInfo, $devMode) {
    if (count($this->_imgs) > 0) {
      foreach ($this->_imgs AS $img) {
      }
    }

    if (count($this->_fonts) > 0) {
      $fonts = implode('|', array_map(function ($font) {
        return str_replace(' ', '+', $font);
      }, $this->_fonts));

      Element::css("http://fonts.googleapis.com/css?family=$fonts")
        ->addToHead();
    }

    if (count($this->_css) > 0) {
      foreach ($this->_css AS $css) {
        // Handle external stylesheets
        if (strpos($css, '://') !== false && strpos($css, 'http') === 0) {
          // This is an external stylesheet
          Element::css($css)->addToHead();

        // Handle stylesheets defined as Compilable objects.  This is usually
        // the case for a resource that is Generateable or requires custom
        // logic for compilation
        } else if (is_object($css)) {
          // FIXME This should be thrown when the resource is added so that
          //       the stack is more relevant
          if (!($css instanceof Compilable)) {
            throw new RuntimeException("Object resources must be compilable");
          }

          if ($devMode) {
            if ($css instanceof Generateable) {
              $css->generate($pathInfo);
            }
            $css->link($pathInfo);
          }

          $css->inc($pathInfo);

        // Resource is defined as a string so use the Resource class to load
        // the resource
        } else {
          // TODO Pull this out of resource so that more control over how
          //      dev mode is handled can be asserted here.
          Resource::load($css);
        }
      }
    }

    if (count($this->_libs) > 0) {
      foreach ($this->_libs AS $lib) {
        if ($devMode) {
          $lib->link($pathInfo, $devMode);
        }
        $lib->inc($pathInfo, $devMode);
      }
    }

    if (count($this->_srvcs) > 0) {
      foreach ($this->_srvcs AS $srvc) {
        ServiceProxy::get($srvc)->addToHead();
      }
    }

    if (count($this->_js) > 0) {
      foreach ($this->_js AS $js) {

        // Handle stylesheets defined as Compilable objects.  This is usually
        // the case for a resource that is Generateable or requires custom
        // logic for compilation
        if (is_object($js)) {
          // FIXME This should be thrown when the resource is added so that
          //       the stack is more relevant
          if (!($js instanceof Compilable)) {
            throw new RuntimeException("Object resources must be compilable");
          }

          if ($devMode) {
            $js->link($pathInfo, $devMode);
          }

          $js->inc($pathInfo, $devMode);

        // This is an external stylesheet, simply include a script element in
        // the page
        } else if (strpos($js, '://') !== false && strpos($js, 'http') === 0) {
          Element::js($js)->addToHead();

        // Resource is defined as a string so use the Resource class to load
        // the resource
        } else {
          // TODO Pull this out of resource so that more control over how
          //      dev mode is handled can be asserted here.
          Resource::load($js);
        }
      }
    }
  }

  public function merge(ResourceSet $resources) {
    $merged = new ResourceSet();
    $merged->addSheets($this->_css);
    $merged->addFonts($this->_fonts);
    $merged->addImages($this->_imgs);
    $merged->addScripts($this->_js);
    $merged->addJsLibs($this->_libs);
    $merged->addServices($this->_srvcs);

    $merged->addSheets($resources->getSheets());
    $merged->addFonts($resources->getFonts());
    $merged->addImages($resources->getImages());
    $merged->addScripts($resources->getScripts());
    $merged->addJsLibs($resources->getJsLibs());
    $merged->addServices($resources->getServices());

    return $merged;
  }

}
