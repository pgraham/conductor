/**
 * This script defines a jQuery plugin which will add mouseover/mouseout
 * handlers to the matched set of elements which toggle an 'over' class
 * on the elements.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var DEFAULT_CLASS = 'over';

  $.fn.notifyover = function (cssClass) {

    cssClass = cssClass || DEFAULT_CLASS;

    return this.each(function () {
      $(this).hover(
        function () {
          $(this).addClass(cssClass);
        },
        function () {
          $(this).removeClass(cssClass);
        }
      );
    });

  };

})( jQuery );
