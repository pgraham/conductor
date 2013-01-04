/**
 * Dialog for updating the current user's password.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
"use strict";
(function (exports, $, CDT, undefined) {

  exports.changePasswordDialog = function () {
    var pwForm = CDT.cmp.changePasswordForm(),
        btns = {},
        cmp = observable({}),
        elm;

    btns[_L('lbl.changepwd').ucfirst()] = function () {
      var indicator = $('<div class="indicator">')
        .append($('<img/>').attr('src', _p('/img/working.gif')))
        .append($('<span/>').text(_L('users.password.updating')))
        .appendTo($(this).parent().find('.ui-dialog-buttonpane'));

      pwForm.submit(function (response) {
        indicator.remove();
        if (response.success) {
          elm.dialog('close');
          cmp.trigger('passwordupdate', response);
        }
      });
    };

    btns[_L('lbl.cancel').ucfirst()] = function () {
      elm.dialog('close');
    };

    elm = $('<div class="change-password"/>')
      .append(pwForm.elm)
      .dialog({
        autoOpen: false,
        buttons: btns,
        dialogClass: 'cdt-changepassword-dialog',
        hide: 400,
        modal: true,
        resizable: false,
        show: 400,
        title: _L('lbl.changepwd').ucfirst(),
        width: 605,
        zIndex: 1,
        close: function () {
          $(this).dialog('destroy').remove();
        }
      });

    return $.extend(cmp, {
      open: function () {
        elm.dialog('open');
      }
    });
  };

} (CDT.ns('CDT.cmp'), jQuery, CDT));
