/**
 * Layout controller.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  var layouts = [];

  CDT.layout = {};

  CDT.layout.register = function (layoutFn) {
    layouts.push(layoutFn);
  };

  CDT.layout.unregister = function (layoutFn) {
    var i, len;
    for (i = 0, len = layouts.length; i < len; i++) {
      if (layouts[i] === layoutFn) {
        layouts.splice(i, 1);
        break;
      }
    }
  };

  CDT.layout.doLayout = function () {
    $.each(layouts, function (idx, fn) {
      fn();
    });
  };

  // Add a resize handler to the window which applies all layout functions
  $(window).resize(function () {
    CDT.layout.doLayout();
  });

} (jQuery, CDT));
