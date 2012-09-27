<?php
namespace ${actorNs};

use \conductor\Auth;
use \oboe\struct\FlowContent;
use \oboe\Element;
use \zpt\cdt\di\Injector;
use \zpt\cdt\html\Page;
use \zpt\cdt\L10N;
use \zpt\cdt\LoginForm;

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
        $this->_loadLogin();
        exit;
      }
    ${fi}

    // Base styles
    Element::css('${cssPath}/reset.css')->addToHead();
    Element::css('${cssPath}/cdt.css')->addToHead();

    // Javascript libraries
    // -------------------------------------------------------------------------

    // DateJS
    Element::js('${jslibPath}/datejs/date.js')->addToHead();

    // JQuery - If dev mode non-minimized version is included
    $this->_loadJQuery();

    // Webshims
    Element::js('${jslibPath}/webshims/polyfiller.js')->addToHead();

    // JQuery Cookie
    Element::js('${jslibPath}/jquery-cookie/jquery.cookie.js')->addToHead();

    // JQuery UI
    Element::css('${jslibPath}/jquery-ui/jquery.ui.css')->addToHead();
    Element::css('${jslibPath}/jquery-ui/themes/${uitheme}/jquery.ui.theme.css')
      ->addToHead();
    Element::js('${jslibPath}/jquery-ui/external/globalize.js')->addToHead();
    Element::js('${jslibPath}/jquery-ui/jquery.ui.js')->addToHead();

    // -------------------------------------------------------------------------

    // Client support scripts
    $lang = L10N::getLang();

    Element::js("${jsPath}/$lang.js")->addToHead();
    Element::js('${jsPath}/base.js')->addToHead();
    ${if:env = dev}
      Element::js('${jsPath}/cdt.core-__init.js')->addToHead();
      Element::js('${jsPath}/cdt.core.data-store.js')->addToHead();
      Element::js('${jsPath}/cdt.core.data-crudProxy.js')->addToHead();
      Element::js('${jsPath}/cdt.core.jquery-working.js')->addToHead();
      Element::js('${jsPath}/cdt.core-date.js')->addToHead();
      Element::js('${jsPath}/cdt.core-eventuality.js')->addToHead();
      Element::js('${jsPath}/cdt.core-hasValue.js')->addToHead();
      Element::js('${jsPath}/cdt.core-layout.js')->addToHead();
      Element::js('${jsPath}/cdt.core-loadCss.js')->addToHead();
      Element::js('${jsPath}/cdt.core-message.js')->addToHead();
      Element::js('${jsPath}/cdt.core-string.js')->addToHead();
    ${else}
      Element::js('${jsPath}/cdt.core.js')->addToHead();
    ${fi}

    ${if:jsappsupport}
      Element::js('${jslibPath}/raphael/raphael.js')->addToHead();

      ${if:env = dev}
        Element::js('${jsPath}/widget-section.js')->addToHead();
        Element::js('${jsPath}/widget-collapsible.js')->addToHead();
        Element::js('${jsPath}/widget-dialog.js')->addToHead();
        Element::js('${jsPath}/widget-floatingmenu.js')->addToHead();
        Element::js('${jsPath}/widget-form.js')->addToHead();
        Element::js('${jsPath}/widget-pager.js')->addToHead();
        Element::js('${jsPath}/widget-list.js')->addToHead();
        Element::js('${jsPath}/widget-icon.js')->addToHead();
        Element::js('${jsPath}/widget-download.js')->addToHead();
      ${else}
        Element::js('${jsPath}/widget.js')->addToHead();
      ${fi}

      Element::css('${cssPath}/jsapp.css')->addToHead();
      Element::js('${jsPath}/jsapp.js')->addToHead();
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
        $this->_loadLogin();
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

  private function _loadJQuery() {
    ${if:env = dev}
      Element::js('${jQueryPath}/jquery.min.js')->addToHead();
    ${else}
      Element::js('${jQueryPath}/jquery.js')->addToHead();
    ${fi}
  }

  private function _loadLogin() {
    Element::css('http://fonts.googleapis.com/css?family=Sorts+Mill+Goudy|Varela')->addToHead();
    Element::css(_P('/css/login.css'))->addToHead();

    $this->_loadJQuery();
    Element::js(_P('/js/login.js'))->addToHead();

    $login = new LoginForm();
    $login->addToBody();
    Page::dump();
  }

  private function _onPageView() {
    foreach ($this->_pageViewListeners as $pageViewListener) {
      $pageViewListener->pageView();
    }
  }
}
