/**
 * This file adds admin controls to CDT JsApp.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {

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
