/**
 * This file adds admin controls to CDT JsApp.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  $(document).ready(function () {
    var viewSite, previewWnd, options, optionsMenu;

    // Initialize the options menu for the app
    // TODO The view site and options button really belong as part of a
    //      cdt-admin JsApp, not part of JsApp itself.
    viewSite = $('<button>View Site</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        if (previewWnd === undefined || previewWnd.closed) {
          previewWnd = window.open(_p('/'), 'preview');
        } else {
          previewWnd.location.reload();
          previewWnd.focus();
        }
      });
    CDT.app.addMenuItem(viewSite);

    options = $('<button>Options</button>')
      .addClass('ui-button-small')
      .button({
        text: false,
        icons: {
          primary: 'ui-icon-gear',
          secondary: 'ui-icon-triangle-1-s'
        }
      })
      .click(function () { optionsMenu.toggleMenu(); });
    CDT.app.addMenuItem(options);

    optionsMenu =
      CDT.floatingmenu({
       position: {
          my: 'right top',
          at: 'right bottom',
          of: options
        }
      })
      .addMenuItem('Global Settings', function () {
        CDT.app.addView('globalSettings', 'Global Settings', 
          CDT.cmp.globalConfigEditor(), true /* Closeable */);
        CDT.app.showView('globalSettings');
      })
      .addMenuItem('Change Password', function () {
        var pwForm = CDT.cmp.changePasswordForm();

        $('<div class="change-password"/>').attr('title', 'Change Password')
          .append( pwForm )
          .dialog({
            resizable: false,
            modal: true,
            width: 605,
            zIndex: 1,
            buttons: {
              'Cancel': function () {
                $(this).dialog('close').dialog('destroy').remove();
              },
              'Change Password': function () {
                var dialog = $(this);
                $.ajax({
                  url: _p('/users/current/password'),
                  type: 'POST',
                  data: $(this).find('form').serialize(),
                  dataType: 'json',
                  success: function (response) {
                    if (response.success) {
                      dialog.dialog('close').dialog('destroy').remove();
                    } else {
                      pwForm.setMessages(response.fieldMsgs);
                    }
                  }
                });
              }
            }
          })
      })
      .addMenuItem('Logout', function () {
        $.ajax({
          url: _p('/logout'),
          type: 'POST',
          success: function () {
            window.location.href = _p('/');
          }
        });
      });

  });
} (jQuery, CDT));
