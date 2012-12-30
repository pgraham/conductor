"use strict";
/**
 * This script adds a global ajax error handler which will display a login form
 * when a request fails with a 401 status. Upon successful login the original
 * request will be re-attempted automatically.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  function attemptLogin(credentials, ajaxSettings) {
    $('body').working();
    $.ajax({
      url: _p('/login'),
      type: 'POST',
      data: credentials,
      success: function (data) {
        if (data.success) {
          $.ajax(ajaxSettings);
        } else {
          showLogin(ajaxSettings, data.msg);
        }
      },
      complete: function () {
        $('body').done();
      }
    });
  }

  function showLogin(ajaxSettings, msg) {
    msg = msg || "You must login in order to perform the requested action.";

    $('<form class="login"/>')
      .append('<div class="message error">' + msg + '</div>')
      .append('<label for="uname">Username</label>')
      .append('<input type="text" name="uname" id="uname"/>')
      .append('<label for="pw">Password</label>')
      .append('<input type="password" name="pw" id="pw"/>')
      .dialog({
        buttons: {
          'Ok': function () {
            attemptLogin($(this).serialize(), ajaxSettings);
            $(this).dialog('close').dialog('destroy').remove();
          },
          'Cancel': function () {
            window.location.reload(true);
          }
        }
      });
  }

  $(document).ready(function () {

    $('body').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
      if (jqXHR.status === 401) {
        showLogin(ajaxSettings);
      }
    });
  });
} (jQuery));
