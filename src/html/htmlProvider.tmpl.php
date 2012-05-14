<?php
namespace zeptech\dynamic\html;

use \conductor\html\Page;
use \conductor\Auth;
use \conductor\PageLoader;
use \oboe\struct\FlowContent;
use \oboe\Element;

/**
 * This is a generated class that populates an conductor\Page instance.
 *
 * DO NOT MODIFY.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ${actor} {

  public function populate (Page $page) {
    ${if:title ISSET}
      $page->setPageTitle('${title}');
    ${fi}

    ${if:template ISSET}
      $page->setTemplate(new \${template}());
    ${fi}

    ${if:auth ISSET}
      if (!Auth::hasPermission('${auth}')) {
        PageLoader::loadLogin();
        exit;
      }
    ${fi}

    // Basic client support
    Element::js('${baseJsPath}')->addToHead();
    Element::css('${resetCssPath}')->addToHead();
    Element::css('${cdtCssPath}')->addToHead();

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
    Element::js('${jqueryWorkingPath}')->addToHead();
    Element::js('${utilityJsPath}')->addToHead();
    Element::js('${jqueryDomPath}')->addToHead();
    Element::js('${cdtJsPath}')->addToHead();

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

    ${if:hasContent}
      $ctntProvider = new \${contentProvider}();
      $page->bodyAdd($ctntProvider->getContent());
    ${fi}
  }
}
