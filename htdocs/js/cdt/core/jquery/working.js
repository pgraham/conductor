/*
 * A jQuery extension for adding a load mask to any element
 * The image for the load mask is expected by default to be found at
 * /img/working.gif but can be overridden by setting jQuery.working.imgPath
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ( $ ) {
  "use strict";

  $.working = {};
  $.working.imgPath = _p('/img/working.gif');

  $.fn.working = function () {

    return this.each(function () {
      var ctx = $(this), mask = ctx.data('working-mask'), img;

      if (mask === undefined) {
        mask = $('<div/>')
          .addClass('ui-widget-overlay')
          .css('opacity', '0.65')
          .css('zIndex', 1)
          .append(
            $('<img/>')
              .css('position', 'absolute')
              .css('opacity', '1')
              .attr('src', $.working.imgPath)
          );

        ctx.data('working-mask', mask);
      }

      mask.appendTo(ctx);
      img = mask.find('img');
      img.position({
        my: 'center',
        at: 'center',
        of: mask
      });
    });
  };

  $.fn.done = function () {

    return this.each(function () {
      var ctx = $(this), mask = ctx.data('working-mask');

      if (mask !== undefined) {
        mask.detach();
      }
    });
  };

})( jQuery );

