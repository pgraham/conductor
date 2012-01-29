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
