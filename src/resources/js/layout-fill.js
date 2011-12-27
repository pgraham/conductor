(function ($, CDT, undefined) {
  "use strict";

  if (CDT.layout === undefined) {
    CDT.layout = {};
  }

  CDT.layout.fill = function (container) {
    var layout = {};

    layout.with = function (selector) {
      var toFill = container.children(selector),
          height = container.height(),
          allocated = 0,
          remaining,
          fill;

      container.children().each(function () {
        if (!$(this).is(selector)) {
          allocated += $(this).outerHeight(true);
        }
      });

      remaining = height - allocated;
      fill = remaining / toFill.length;
      toFill.height(fill);
    }

    return layout;
  };
} (jQuery, CDT));
