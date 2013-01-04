"use strict";
/**
 * This script adds a global ajax error handler which will display a login form
 * when a request fails with a 401 status. Upon successful login the original
 * request will be re-attempted automatically.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var noAuthQueue = [], dialog;

  $(document).ready(function () {
    dialog = $('form.login').dialog({
      autoOpen: false,
      buttons: {
        'Ok': function () {
          attemptLogin($(this).serialize());
          $(this).dialog('close');
        },
        'Cancel': function () {
          window.location.reload(true);
        }
      },
      hide: 400,
      modal: true,
      show: 400,
      title: _L('auth.authRequired.title'),
      width: 500,
      open: function (event, ui) {
        var w = $(this).width();
        $(this).children('input').outerWidth(w);
      }
    });
  });

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
    dialog.dialog('open').find('.cdt-msg').text(msg || _L('auth.authRequired'));
  }

  $(document).ready(function () {

    $('body').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
      if (jqXHR.status === 401) {
        queueNoAuth(ajaxSettings);
      }
    });
  });
} (jQuery));
