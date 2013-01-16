/**
 * Layout controller.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ( $ ) {
  "use strict";

  function doFill(container, sel, type) {
    var toGrow = typeof sel === 'string' ? container.children(sel) : sel,
        ucType = type.charAt(0).toUpperCase() + type.slice(1),
        fillAmount = container[type](),
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
        allocated += $this[ 'outer' + ucType ](true);
      }
    });

    remaining = fillAmount - allocated;
    fill = Math.floor(remaining / toGrow.filter(':visible').length);
    toGrow.each(function () {
      $(this)[ 'outer' + ucType ](fill, true);
    });

    return toGrow;
  }

  function vFill (container, sel) {
    doFill(container, sel, 'height')
      .css('overflow', 'auto')
      .css('overflow-x', 'hidden');
  }

  function hFill (container, sel) {
    doFill(container, sel, 'width');
  }

  $.layouts = {
    fillWith: vFill,
    'v-fill': vFill,
    'h-fill': hFill
  };

  $.fn.layout = function (type, data) {

    if (type === null) {
      return this.each(function () {
        $(this).removeData('layout-fn');

        // Clear the layout on all descendants recursively
        $(this).children().layout(null);
      });
    }

    if (type) {
      return this.each(function () {
        var ctx = $(this), fn;

        if ($.isFunction(type)) {
          fn = function () {
            type.apply(this, [ ctx.width(), ctx.height() ].concat(data));
          };
        } else {
          fn = function () {
            $.layouts[type](ctx, data);
          };
        }

        ctx.data('layout-fn', fn);
      });
    }

    return this.each(function () {
      var ctx = $(this), fn = ctx.data('layout-fn');

      if (fn) {
        fn.call(this);
      }

      // Layout all children elements that also have a layout defined
      ctx.children().layout();
    });
  };

  // Add a resize handler to the window which applies all layout functions
  $(window).resize(function () {
    $('body').layout();
  });

} ( jQuery ));
