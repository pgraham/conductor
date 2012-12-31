"use strict";
/**
 * This script adds a global ajax error handler which will display a login form
 * when a request fails with a 401 status. Upon successful login the original
 * request will be re-attempted automatically.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var noAuthQueue = [];

  function attemptLogin(credentials) {
    $('body').working();
    $.ajax({
      url: _p('/login'),
      type: 'POST',
      data: credentials,
      success: function (data) {
        if (data.success) {
          $.each(noAuthQueue, function () {
            $.ajax(this);
          });
          noAuthQueue = [];
        } else {
          showLogin(data.msg);
        }
      },
      complete: function () {
        $('body').done();
      }
    });
  }

  function queueNoAuth(ajaxSettings) {
    noAuthQueue.push(ajaxSettings);
    if (noAuthQueue.length === 1) {
      showLogin();
    }
  }

  function showLogin(msg) {

    msg = msg || _L('auth.authRequired');

    $('<form class="login"/>')
      .append('<div class="cdt-msg error">' + msg + '</div>')
      .append('<label for="uname">' + _L('lbl.username') + '</label>')
      .append('<input type="text" name="uname" id="uname"/>')
      .append('<label for="pw">' + _L('lbl.password') + '</label>')
      .append('<input type="password" name="pw" id="pw"/>')
      .dialog({
        buttons: {
          'Ok': function () {
            attemptLogin($(this).serialize());
            $(this).dialog('close').dialog('destroy').remove();
          },
          'Cancel': function () {
            window.location.reload(true);
          }
        },
        hide: 'fade',
        modal: true,
        show: 'fade',
        title: _L('auth.authRequired.title'),
        width: 500,
        open: function (event, ui) {
          var w = $(this).width();
          $(this).children('input').outerWidth(w);
        }
      });
  }

  $(document).ready(function () {

    $('body').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
      if (jqXHR.status === 401) {
        queueNoAuth(ajaxSettings);
      }
    });
  });
} (jQuery));
