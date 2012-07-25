/**
 * This script defines a jQuery plugin that transforms the set of matched
 * elements into a toggle button.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var ON = true, OFF = false;

  $.fn.togglify = function (opts) {

    var settings = $.extend({
      'on'  : 'On',
      'off' : 'Off'
    }, opts);

    return this.each(function () {
      var $this = $(this), ini;

      if ($this.text() === settings.on) {
        ini = ON;
      } else {
        ini = OFF;
        $this.text(settings.off);
      }

      $this.data('toggle-state', ini);

      $this.click(function () {
        if ($this.data('toggle-state')) {
          $this.trigger('toggleOff');
        } else {
          $this.trigger('toggleOn');
        }
      });

      $this.bind('toggleOff', function () {
        $this.data('toggle-state', OFF);
        $this.text(settings.off);
      });

      $this.bind('toggleOn', function () {
        $this.data('toggle-state', ON);
        $this.text(settings.on);
      });
    });

  };

})( jQuery );
