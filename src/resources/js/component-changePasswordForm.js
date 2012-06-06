CDT.ns('CDT.cmp');

/**
 * Form for request the current user's password be updated.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {

  CDT.cmp.changePasswordForm = function () {
    var elm;

    elm = CDT.widget.form()
      .addPassword('curPw', 'Current Password')
      .addPassword('newPw', 'New Password')
      .addPassword('confirmPw', 'Confirm Password')

    elm.find('input').tooltip();

    return $.extend(elm, {
      setMessages: function (msgs) {
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
    });
  };
} (jQuery, CDT));
