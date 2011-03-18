/**
 * This script provides support for the Conductor framework.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
var Conductor = (function ($) {

  var curPage,
      curPageSel,

      /* Internal function for accessing the loadPage RPC */
      loadPage = function (pageId, sel) {
        ConductorService.loadPage(pageId, function (data) {
          curPage = pageId;
          curPageSel = sel;

          $(sel).html(data);
        });
      };

  return {
    /**
     * Initialize the conductor client.  This function is called automatically
     * and clobbers itself so that it can't be called twice.
     */
    init: function (curPageId) {
      curPage = curPageId;

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
      this.init = function (curPageId) {
        curPage = curPageId;
      }
    },

    /**
     * Load the page with the given id.
     *
     * @param string pageId
     * @param string sel Selector for the element with which to populate the
     *   loaded page.
     */
    loadPage: function (pageId, sel) {
      loadPage(pageId, sel);
    }
  }
}(jQuery));

$(document).ready(function () {
  Conductor.init();
});
