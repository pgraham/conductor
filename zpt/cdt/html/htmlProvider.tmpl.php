<?php
namespace zeptech\dynamic\html;

use \zpt\cdt\di\Injector;
use \zpt\cdt\html\Page;
use \conductor\Auth;
use \conductor\PageLoader;
use \oboe\struct\FlowContent;
use \oboe\Element;

/**
 * This is a generated class that populates a conductor\Page instance.
 *
 * DO NOT MODIFY.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ${actor} {

  /** @Injected */
  private $_authProvider;

  /**
   * @Injected
   * @Collection zpt\cdt\html\PageViewListener
   */
  private $_pageViewListeners;

  public function populate(Page $page, array $query = null) {
    ${if:title ISSET}
      $page->setPageTitle('${title}');
    ${fi}

    ${if:template ISSET}
      $tmpl = new \${template}();
      ${if:tmplDependencies ISSET}
        Injector::inject($tmpl, ${php:tmplDependencies});
      ${fi}
      $page->setTemplate($tmpl);
    ${fi}

    ${if:auth ISSET}
      if (!$this->_authProvider->hasPermission('${auth}')) {
        PageLoader::loadLogin();
        exit;
      }
    ${fi}

    // Basic client support
    Element::js('${jsPath}/base.js')->addToHead();
    Element::css('${cssPath}/reset.css')->addToHead();
    Element::css('${cssPath}/cdt.css')->addToHead();

    // Javascript libraries
    PageLoader::loadDateJs();
    PageLoader::loadJQuery();
    PageLoader::loadJQueryCookie();
    ${if:uitheme ISSET}
      PageLoader::loadJQueryUi('${uitheme}');
    ${else}
      PageLoader::loadJQueryUi();
    ${fi}

    // Client support scripts
    Element::js('${jsPath}/jquery.working.js')->addToHead();
    Element::js('${jsPath}/utility.js')->addToHead();
    Element::js('${jsPath}/utility-loadCss.js')->addToHead();
    Element::js('${jsPath}/jquery-dom.js')->addToHead();
    Element::js('${jsPath}/conductor.js')->addToHead();
    Element::js('${jsPath}/widget-message.js')->addToHead();
    Element::js('${jsPath}/layout.js')->addToHead();

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
