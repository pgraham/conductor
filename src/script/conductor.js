/**
 * This script provides support for the Conductor framework.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
var Conductor = (function ($) {

  var loginHandlers = [];

  return {
    /**
     * Initialize the conductor client.  This function is called automatically
     * and clobbers itself so that it can't be called twice.
     */
    init: function () {
      // Add live click handler for the login form.
      $('#login.async').find('.cdt-Submit').live('click', function () {
        var form     = $('#login.async'),
            username = form.find('input[name="uname"]'),
            password = form.find('input[name="pw"]'),
            error    = form.find('.cdt-Error'),
            count    = loginHandlers.length,
            i;

        error.empty();
        
        ConductorService.login(username.val(), password.val(), function (resp) {
          if (resp.msg === null) {
            for (i = 0; i < count; i++) {
              loginHandlers[i].call();
            }
          } else {
            error.text(resp.msg);
            username.focus().select();
          }
        });
      });

      // Don't let initialization happen twice
      this.init = $.noop;
    },

    /**
     * Register a function to invoke after a successful login attempt.
     *
     * @param function handler
     */
    onLogin: function (handler) {
      loginHandlers.push(handler);
    }
  }
}(jQuery));

$(document).ready(function () {
  Conductor.init();
});
