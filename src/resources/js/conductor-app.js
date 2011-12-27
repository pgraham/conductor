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
    // Initialize the tab panel that will contain the app
    tabs = $('<div><ul/></div>')
      .css({
        'position': 'absolute',
        'top': '10px',
        'bottom': '10px',
        'left': '10px',
        'right': '10px'
      })
      .appendTo($('body'))
      .tabs();

    tabs.bind('tabsshow', function (event, ui) {
      CDT.app.fire({
        type: 'view-change',
        id: ui.panel.id,
        ui: ui
      });
    });
  });

} (jQuery.noConflict(), CDT));
