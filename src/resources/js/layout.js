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
