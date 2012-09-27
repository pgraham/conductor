/**
 * This script provides support for the Conductor framework.
 * It is a compiled script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
/**
 * Page loading.  Hooks in with browser history API.
 *
 * TODO - This is not used by any sites and is broken but is kept around as may
 *        eventually be useful.
 */
(function ($, exports, undefined) {
  "use strict";

  var curPage, curPageSel, loadPage,

      /* Function for initializing the client */
      init,

      /*
       * Function to set the current page id, this should only be called on
       * startup.
       */
      setPageId;

  // Make exported API observable
  eventuality(exports);

  /* Internal function for accessing the loadPage RPC */
  loadPage = function (path, sel) {
    $(sel).working().load(_p(path + '.frag'), /* No data */, function () {
      curPage = path;
      curPageSel = sel;

      $(sel).done();
      exports.fire({
        type   : 'pageLoad',
        pageId : path
      });
    });
  };

  exports.loadPage = function (path, sel) {
    history.pushState({
      pageId : path,
      sel    : sel
    }, '', window.location.href);

    loadPage(path, sel);
  };

  // Bind to back, forward buttons to load the requested page
  $(window).bind('popstate', function (e) {
    var state = e.originalEvent.state;
    if (state) {
      loadPage(state.pageId, state.sel);
    } else {
      // This is an initial page load
      // TODO - I don't think this works
      history.replaceState({
        pageId : curPage,
        sel    : curPageSel
      });
    }
  });

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
    setPageId();
  };
  CDT.init = init;

} (jQuery, CDT));

$(document).ready(function () {
  CDT.init();
});
