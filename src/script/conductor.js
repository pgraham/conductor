/**
 * This script provides support for the Conductor framework.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
var CDT = {};

(function ($, CDT, undefined) {

  var curPage, curPageSel;

  /* Internal function for accessing the loadPage RPC */
  var loadPage = function (pageId, sel) {
    ConductorService.loadPage(pageId, function (data) {
      curPage = pageId;
      curPageSel = sel;

      $(sel).html(data);
    });
  };
  CDT.loadPage = loadPage;

  /**
   * Initialize the conductor client.  This function is called automatically
   * and clobbers itself so that it can't be called twice.
   */
  var init = function (curPageId) {
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
  };
  CDT.init = init;

} (jQuery, CDT));

$(document).ready(function () {
  CDT.init();
});

// Add some handy extensions, a la Crockford
if (typeof Object.create !== 'function') {
  Object.create = function (o) {
    var F = function () {};
    F.prototype = o;
    return new F();
  };
}

if (typeof Function.prototype.method !== 'function') {
  Function.prototype.method = function (name, func) {
    this.prototype[name] = func;
    return this;
  };
}

// Some parts

var hasValue = function (that, spec) {
  var reset = function () {
    spec.setValue(spec.initial);
  }

  that.getValue = spec.getValue;
  that.setValue = spec.setValue;
  that.reset = reset;

  // Set the initial value
  spec.setValue(spec.initial);
}
