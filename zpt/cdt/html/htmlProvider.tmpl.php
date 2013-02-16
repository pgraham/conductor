<?php
namespace ${actorNs};

use \conductor\Auth;
use \oboe\struct\FlowContent;
use \oboe\Element;
use \zpt\cdt\di\Injector;
use \zpt\cdt\html\Page;
use \zpt\cdt\L10N;
use \zpt\cdt\LoginForm;
use \zpt\cdt\LoginFormAsync;

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
    // -------------------------------------------------------------------------
    Element::css('${cssPath}/reset.css')->addToHead();
    Element::css('${cssPath}/cdt.css')->addToHead();

    ${each:coreCss as css}
      Element::css('${cssPath}/${css}')->addToHead();
    ${done}
    ${if:sheets ISSET}
      ${each:sheets as css}
        Element::css('${css}')->addToHead();
      ${done}
    ${fi}

    // Javascript libraries
    // -------------------------------------------------------------------------
    // Non-jquery
    Element::js('${jslibPath}/date.js')->addToHead();

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

    // Load language script
    $lang = L10N::getLang();
    Element::js("${jsPath}/$lang.js")->addToHead();

    // Client support scripts
    Element::js('${jsPath}/base.js')->addToHead();
    ${each:coreScripts as script}
      Element::js('${jsPath}/${script}')->addToHead();
    ${done}
    ${each:utilScripts as script}
      Element::js('${jsPath}/${script}')->addToHead();
    ${done}

    $this->_includeJsLibs();

    ${if:jsappsupport}
      Element::js('${jslibPath}/raphael.js')->addToHead();
      Element::js('${jsPath}/cdt/raphael-util.js')->addToHead();

      ${each:widgetCss as sheet}
        Element::css('${cssPath}/${sheet}')->addToHead();
      ${done}
      Element::css('${cssPath}/jsapp.css')->addToHead();

      ${each:widgetScripts as script}
        Element::js('${jsPath}/${script}')->addToHead();
      ${done}
      Element::js('${jsPath}/jsapp.js')->addToHead();
    ${fi}

    ${if:fonts ISSET}
      Element::css("http://fonts.googleapis.com/css?family=${fonts}")->addToHead();
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

    // Add an asynchronous login form that will be initially hidden so that it
    // can be autocompleted by the browser.
    $page->bodyAdd(new LoginFormAsync());
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

  private function _includeJsLibs() {
    ${each:jslibs as jslib}
      ${if:jslib = epiceditor}
        Element::js('${jslibPath}/epiceditor/epiceditor.js')->addToHead();
        Element::js('${jslibPath}/epiceditor/epiceditor.css');
      ${elseif:jslib = highlight}
        Element::js('${jslibPath}/highlight/highlight.js')->addToHead();
        Element::css('${jslibPath}/highlight/highlight.css')->addToHead();
      ${elseif:jslib = raphael}
        Element::js('${jslibPath}/raphael.js')->addToHead();
        Element::js('${jsPath}/cdt/raphael-util.js')->addToHead();
      ${else}
        Element::js('${jslibPath}/${jslib}.js')->addToHead();
      ${fi}
    ${done}

    #{ each: jslibs as jslib
      #{ switch: jslib
        #{ case: arboreal }
        // Element::js(_P('/jslib/arboreal.js'))->addToHead();

        #{ case: epiceditor }
        // Element::js(_P('/jslib/epiceditor/epiceditor.js'))->addToHead();
        // Element::js(_P('/jslib/epiceditor/epiceditor.css'));

        #{ case: markdown }
        // Element::js(_P('/jslib/markdown.js'))->addToHead();

        #{ case: raphael }
        // Element::js(_P('/jslib/raphael.js'))->addToHead();

        #{ case: highlight }
        // Element::js(_P('/jslib/highlight/highlight.js'))->addToHead();
        // Element::css(_P('/jslib/highlight/highlight.css'))->addToHead();

      #}
    #}
  }

  private function _loadJQuery() {
    ${if:env = dev}
      Element::js('${jQueryPath}/jquery.js')->addToHead();
    ${else}
      Element::js('${jQueryPath}/jquery.min.js')->addToHead();
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
