/**
 * Form for request the current user's password be updated.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
"use strict";
(function (exports, $, CDT, undefined) {

  exports.changePasswordForm = function () {
    var elm;

    function setMessages(msgs) {
      msgs = msgs || {};

      $.each(msgs, function (idx, msg) {
        elm.find('[name=' + idx + ']')
          .addClass('invalid')
          .attr('title', msg)
          .tooltip('enable')
          .keypress(function () {
            $(this)
              .removeClass('invalid')
              .removeAttr('title')
              .tooltip('disable');
          });
      });
    }

    elm = CDT.widget.formBuilder()
      .addPassword('curPw', 'Current Password')
      .addPassword('newPw', 'New Password')
      .addPassword('confirmPw', 'Confirm Password')
      .build();

    elm.find('input').tooltip();

    return {
      elm: elm,
      submit: function (cb) {
        cb = cb || $.noop;
        $.ajax({
          url: _p('/users/current/password'),
          type: 'POST',
          data: elm.serialize(),
          dataType: 'json',
          success: function (response) {
            if (!response.success) {
              setMessages(response.fieldMsgs);
            }
            cb(response);
          },
          error: function (jqXHR, textStatus, errorThrown) {
            cb({
              success: false,
              msg: {
                text: errorThrown,
                type: 'error'
              }
            });
          }
        });
      }
    };
  };

} (CDT.ns('CDT.cmp'), jQuery, CDT));
