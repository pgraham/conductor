/**
 * This script defines a jQuery plugin which will add mouseover/mouseout
 * handlers to the matched set of elements which toggle an 'over' class
 * on the elements.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var addOver, removeOver;

  addOver = function () {
    $(this).addClass('over');
  };

  removeOver = function () {
    $(this).removeClass('over');
  };

  $.fn.notifyOver = function () {

    return this.each(function () {
      var $this = $(this);

      if ($this.data('notifying-over') === true) {
        return;
      }

      $this.data('notifying-over', true);

      $this.mouseover(addOver);
      $this.mouseout(removeOver);

    });

  };

  $.fn.notifyOverLive = function () {
    if (this.data('notifying-over-live') === true) {
      return;
    }

    this.data('notifying-over-live', true);
    this.live('mouseover', addOver);
    this.live('mouseout', removeOver);
    return this;
  };

})( jQuery );
