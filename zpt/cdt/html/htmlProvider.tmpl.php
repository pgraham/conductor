<?php
namespace ${actorNs};

use \conductor\Auth;
use \oboe\struct\FlowContent;
use \oboe\Element;
use \zpt\cdt\di\Injector;
use \zpt\cdt\html\Page;
use \zpt\cdt\L10N;
use \zpt\cdt\PageLoader;

/**
 * This is a generated class that populates a conductor\Page instance.
 *
 * DO NOT MODIFY.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ${actorClass} {

  /** @Injected */
  private $_authProvider;

  /**
   * @Injected
   * @Collection zpt\cdt\html\PageViewListener
   */
  private $_pageViewListeners;

  public function populate(Page $page, array $query = null) {
    ${if:env = dev}
      $page->setCaptureDebug(true);
    ${else}
      $page->setCaptureDebug(false);
    ${fi}

    ${if:title ISSET}
      $page->setPageTitle('${title}');
    ${fi}

    ${if:template ISSET}
      $tmpl = new \${template}();
      ${if:tmplDependencies ISSET}
        Injector::inject($tmpl, ${php:tmplDependencies});
      ${fi}
    ${fi}

    ${if:auth ISSET}
      if (!$this->_authProvider->hasPermission('${auth}')) {
        PageLoader::loadLogin();
        exit;
      }
    ${fi}

    // Base styles
    Element::css('${cssPath}/reset.css')->addToHead();
    Element::css('${cssPath}/cdt.css')->addToHead();

    // Javascript libraries
    PageLoader::loadDateJs();
    PageLoader::loadJQuery();
    Element::js('${jslibPath}/webshims/polyfiller.js')->addToHead();
    PageLoader::loadJQueryCookie();
    ${if:uitheme ISSET}
      PageLoader::loadJQueryUi('${uitheme}');
    ${else}
      PageLoader::loadJQueryUi();
    ${fi}

    // Client support scripts
    $lang = L10N::getLang();

    Element::js("${jsPath}/$lang.js")->addToHead();
    Element::js('${jsPath}/base.js')->addToHead();
    ${if:env = dev}
      Element::js('${jsPath}/cdt-__init.js')->addToHead();
      Element::js('${jsPath}/cdt.jquery-dom.js')->addToHead();
      Element::js('${jsPath}/cdt.jquery-working.js')->addToHead();
      Element::js('${jsPath}/cdt.util-eventuality.js')->addToHead();
      Element::js('${jsPath}/cdt.util-hasValue.js')->addToHead();
      Element::js('${jsPath}/cdt.util-string.js')->addToHead();
      Element::js('${jsPath}/cdt.util-date.js')->addToHead();
      Element::js('${jsPath}/cdt.util-loadCss.js')->addToHead();
      Element::js('${jsPath}/cdt.util-message.js')->addToHead();
      Element::js('${jsPath}/cdt.util-layout.js')->addToHead();
      //Element::js('${jsPath}/cdt-ajaxify.js')->addToHead();
    ${else}
      Element::js('${jsPath}/cdt.js')->addToHead();
    ${fi}

    ${if:jsappsupport}
      ${if:jsapptheme ISSET}
        PageLoader::loadJsAppSupport('${jsapptheme}');
      ${else}
        PageLoader::loadJsAppSupport();
      ${fi}
    ${fi}

    ${if:fonts ISSET}
      Element::css("http://fonts.googleapis.com/css?family=${fonts}")->addToHead();
    ${fi}

    ${if:sheets ISSET}
      ${each:sheets as css}
        Element::css('${css}')->addToHead();
      ${done}
    ${fi}

    // Javascripts
    ${if:jscripts ISSET}
      ${each:jscripts as jscript}
        Element::js('${jscript}')->addToHead();
      ${done}
    ${fi}

    ${if:template}
      $page->setTemplate($tmpl);
    ${fi}
    $page->bodyAdd($this->getFragment($query));
  }

  public function getFragment($query) {
    ${if:auth ISSET}
      if (!$this->_authProvider->hasPermission('${auth}')) {
        PageLoader::loadLogin();
        exit;
      }
    ${fi}

    $ctnt = '';
    ${if:hasContent}
      $ctntProvider = new \${contentProvider}();
      Injector::inject($ctntProvider, ${php:dependencies});
      $ctnt = $ctntProvider->getContent($query);
    ${fi}

    // Invoke any registered page view listeners
    $this->_onPageView();

    return $ctnt;
  }

  public function setAuthProvider($authProvider) {
    $this->_authProvider = $authProvider;
  }

  public function setPageViewListeners(array $pageViewListeners) {
    $this->_pageViewListeners = $pageViewListeners;
  }

  private function _onPageView() {
    foreach ($this->_pageViewListeners as $pageViewListener) {
      $pageViewListener->pageView();
    }
  }
}
