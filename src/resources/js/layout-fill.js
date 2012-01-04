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
      toFill.each(function () {
        var $this = $(this), margin, border, padding;

        margin = parseInt($this.css('margin-top'), 10) +
                 parseInt($this.css('margin-bottom'), 10);
        border = parseInt($this.css('border-top-width'), 10) +
                 parseInt($this.css('border-bottom-width'), 10);
        padding = parseInt($this.css('padding-top'), 10) + 
                  parseInt($this.css('padding-bottom'), 10);

        $this.height(fill - (margin + border + padding));
      });

      toFill
        .css('overflow', 'auto')
        .css('overflow-x', 'hidden');
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
