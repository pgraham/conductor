/**
 * This javascript provides a basic shell for building a javascript web-app.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  var tabs;

  CDT.app = {};
  eventuality(CDT.app);

  CDT.app.addView = function (id, lbl, elm) {
    var view, paddingTop, paddingBottom, paddingLeft, paddingRight;

    if (tabs === undefined) {
      // TODO Queue the view to be added once the document is ready
      return;
    }

    view = $('<div/>')
        .attr('id', id)
        .append(elm)
        .appendTo(tabs);
    tabs.tabs('add', '#' + id, lbl);

    paddingTop = view.css('padding-top');
    paddingBottom = view.css('padding-bottom');
    paddingLeft = view.css('padding-left');
    paddingRight = view.css('padding-right');

    elm.css({
      'position': 'absolute',
      'top': paddingTop,
      'bottom': paddingBottom,
      'left': paddingLeft,
      'right': paddingRight
    });
  }

  $(document).ready(function () {
    var options, logout, viewSite, previewWnd;

    // Initialize the options menu for the app
    logout = $('<button>Logout</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        window['LoginService'].logout(function () {
          window.location.href = CDT.resourcePath('/index.php');
        });
      });
    viewSite = $('<button>View Site</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        if (previewWnd === undefined || previewWnd.closed) {
          previewWnd = window.open(CDT.resourcePath('/index.php'), 'preview');
        } else {
          previewWnd.location.reload();
          previewWnd.focus();
        }
      });

    options = $('<div class="ui-widget-header"/>')
      .append(logout)
      .append(viewSite)
      .appendTo('body');

    // Initialize the tab panel that will contain the app
    tabs = $('<div id="cdt-app-container"><ul/></div>')
      .css({
        'position': 'absolute',
        'top': options.outerHeight(),
        'bottom': 0,
        'left': 0,
        'right': 0
      })
      .appendTo($('body'))
      .tabs();

    tabs.bind('tabsshow', function (event, ui) {
      CDT.app.fire({
        type: 'view-change',
        id: ui.panel.id,
        ui: ui
      });

    // TODO Why isn't this working?  The class is removed as expected but is
    //      added back later
    tabs.children().filter('.ui-widget-header').removeClass('ui-corner-all');
    });
  });

} (jQuery.noConflict(), CDT));
