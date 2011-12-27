(function ($, CDT, undefined) {
  "use strict";

  var layouts = [];

  if (CDT.layout === undefined) {
    CDT.layout = {};
  }

  CDT.layout.fill = function (container) {
    var layout = {}, sel, apply;

    apply = function () {
      var toFill = container.children(sel),
          height = container.height(),
          allocated = 0,
          remaining,
          fill;

      container.children().each(function () {
        if (!$(this).is(sel)) {
          allocated += $(this).outerHeight(true);
        }
      });

      remaining = height - allocated;
      fill = remaining / toFill.length;
      toFill.height(fill);
    };

    // Push the apply function onto the list of actions to perform when the
    // window is resized
    layouts.push(apply);

    layout.with = function (selector) {
      sel = selector;
      apply();
    }

    return layout;
  };

  // Add a resize handler to the window which applies all layout functions
  // TODO Move this into a generic location so that other layout types can
  //      make use of this functionality
  $(window).resize(function () {
    $.each(layouts, function (idx, fn) {
      fn();
    });
  });

} (jQuery, CDT));
