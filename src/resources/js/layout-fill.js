(function ($, CDT, undefined) {
  "use strict";

  var cache = [];

  CDT.layout.fill = function (container) {
    var layout = {}, sel, apply, i, len;

    for (i = 0, len = cache.length; i < len; i++) {
      if (cache[i].container == container) {
        return cache[i].layout;
      }
    }

    apply = function () {
      var toFill = typeof sel === 'string' ? container.children(sel) : sel,
          height = container.height(),
          allocated = 0,
          remaining,
          fill;

      container.children().each(function () {
        var $this = $(this);
        if (!$this.is(sel)) {
          if (( $this.css('position') === 'static' ||
                $this.css('position') === 'relative') &&
              $this.css('float') === 'none')
          {
            allocated += $(this).outerHeight(true);
          }
        }
      });

      remaining = height - allocated;
      fill = remaining / toFill.length;
      toFill.each(function () {
        $(this).outerHeight(fill, true);
      });

      toFill
        .css('overflow', 'auto')
        .css('overflow-x', 'hidden');
    };

    // Register the layout with the layout manager so that it is reapplied when
    // the window is resized.
    CDT.layout.register(apply);

    layout.with = function (selector) {
      sel = selector;
      apply();
    }

    cache.push({
      container: container,
      layout: layout
    });

    return layout;
  };

} (jQuery, CDT));
