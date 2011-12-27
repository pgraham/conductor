/**
 * This javascript provides a basic shell for building a javascript web-app.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  var tabs;

  CDT.app = {};

  CDT.app.addView = function (id, lbl, elm) {
    tabs.append( $('<div/>').attr('id', id).append(elm) );
    tabs.tabs('add', '#' + id, lbl);
  }

  $(document).ready(function () {
    // Initialize the tab panel that will contain the app
    tabs = $('<div><ul/></div>')
      .css({
        'margin': '10px'
      })
      .appendTo($('body'))
      .tabs();
  });

} (jQuery.noConflict(), CDT));
