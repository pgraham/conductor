/**
 * This script provides support for the Conductor framework.
 * It is a compiled script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
var CDT = {};

/**
 * Page loading.  Hooks in with browser history API.  This needs an overhaul.
 */
(function ($, CDT, undefined) {
  "use strict";

  var curPage, curPageSel, loadPage,

      /* Function for initializing the client */
      init,

      /* Function for retrieving the base path for resources */
      resourcePath,

      /*
       * Function to set the current page id, this should only be called on
       * startup.
       */
      setPageId;

  // Make CDT observable
  eventuality(CDT);

  /* Internal function for accessing the loadPage RPC */
  loadPage = function (pageId, sel) {
    $(sel).working();
    ConductorService.loadPage(pageId, function (data) {
      curPage = pageId;
      curPageSel = sel;

      $(sel).html(data).done();
      CDT.fire({
        type   : 'pageLoad',
        pageId : pageId
      });
    });
  };

  CDT.loadPage = function (pageId, sel) {
    history.pushState({
      pageId : pageId,
      sel    : sel
    }, '', window.location.href);

    loadPage(pageId, sel);
  };

  // Bind to back, forward buttons to load the requested page
  $(window).bind('popstate', function (e) {
    var state = e.originalEvent.state;
    if (state) {
      loadPage(state.pageId, state.sel);
    } else {
      // This is an initial page load
      history.replaceState({
        pageId : curPage,
        sel    : curPageSel
      });
    }
  });

  CDT.checkResponse = function (response) {
    if (!response || response.msg === undefined) {
      return true;
    }

    if ($.isPlainObject(response.msg)) {
      return response.msg.type !== 'error';
    }

    // A message without a type is by default an error message.
    return false;
  };

  setPageId = function (curPageId, sel) {
    if (curPageId === undefined) {
      return;
    }
    curPage = curPageId;
    curPageSel = sel;

    CDT.fire({
      type   : 'page-load',
      pageId : curPageId
    });
  };

  /**
   * Initialize the conductor client.  This function is called automatically
   * and clobbers itself so that it can't be called twice.
   */
  init = function (curPageId, sel) {
    setPageId(curPageId, sel);

    // Add live click handler for the login form.
    $('#login.async').find('.cdt-Submit').live('click', function () {
      var form     = $('#login.async'),
          username = form.find('input[name="uname"]'),
          password = form.find('input[name="pw"]'),
          error    = form.find('.cdt-Error');

      error.empty();
      
      ConductorService.login(username.val(), password.val(), function (resp) {
        if (resp.msg === null) {
          loadPage(curPage, curPageSel);
        } else {
          error.text(resp.msg);
          username.focus().select();
        }
      });
    });

    // Don't let initialization happen twice but still allow the initial page
    // to be set
    CDT.init = setPageId;
  };
  CDT.init = init;

} (jQuery, CDT));

$(document).ready(function () {
  CDT.init();
});

// Override the jquery.working plugin's path for the loading image to account
// for the site's configured web root
// TODO Should this be here?
jQuery.working.imgPath = _p(jQuery.working.imgPath);

/**
 * CDT.ns - ensure declared namespace exists.
 */
(function (CDT, undefined) {

  CDT.ns = function (ns) {
    var parts = ns.split('.'), o = window, i, len;

    for (i = 0, len = parts.length; i < len; i++) {
      o[parts[i]] = o[parts[i]] || {};
      o = o[parts[i]];
    }

    return o;
  };
} (CDT));
