/**
 * Layout controller.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ( $ ) {
  "use strict";

  function fillWith (container, sel) {
    var toFill = typeof sel === 'string' ? container.children(sel) : sel,
        height = container.height(),
        allocated = 0,
        remaining,
        fill;

    container.children().each(function () {
      var $this = $(this);

      if ($this.is(sel)) {
        return;
      }
      
      if (( $this.css('position') === 'static' ||
            $this.css('position') === 'relative') &&
          $this.css('float') === 'none')
      {
        allocated += $this.outerHeight(true);
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
  }

  $.layouts = {
    fillWith: fillWith
  };

  $.fn.layout = function (type, params) {

    if (type === null) {
      return this.each(function () {
        $(this).removeData('layout-fn');

        // Clear the layout on all descendants recursively
        $(this).children().layout(null);
      });
    }

    if (type) {
      return this.each(function () {
        var ctx = $(this);

        ctx.data('layout-fn', function () {
          $.layouts[type](ctx, params);
        });
      });
    }

    return this.each(function () {
      var ctx = $(this), fn = ctx.data('layout-fn');

      if (fn) {
        fn();
      }

      // Layout all children elements that also have a layout defined
      ctx.children().layout();
    });
  };

  // Add a resize handler to the window which applies all layout functions
  $(window).resize(function () {
    $(document).layout();
  });

} ( jQuery ));
