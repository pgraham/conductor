/**
 * jQuery plugin that will retrieve the amount of an elements outerWidth that
 * is composed of margin, border and padding (MBP).
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {
  "use strict";

  /**
   * Retrieve the MBP of the first element in the set of matched elements.
   *
   * Returns an array with width and height properties.
   */
  $.fn.mbp = function () {
    return {
      width: this.mbpWidth(),
      height: this.mbpHeight()
    };
  };

  /**
   * Retrieve the MBP width of the first element in the set of matched elements.
   */
  $.fn.mbpWidth = function () {
    return this.marginWidth() + this.borderWidth() + this.paddingWidth();
  };

  /**
   * Retrieve the MBP height of the first element in the set of matched
   * elements.
   */
  $.fn.mbpHeight = function () {
    return this.marginHeight() + this.borderHeight() + this.paddingHeight();
  };

  /**
   * Retrieve the margin width of the first element in the set of matched
   * elements.
   */
  $.fn.marginWidth = function () {
    return parseInt(this.css('margin-left'), 10) +
           parseInt(this.css('margin-right'), 10);
  };

  /**
   * Retrieve the margin height of the first element in the set of matched
   * elements.
   */
  $.fn.marginHeight = function () {
    return parseInt(this.css('margin-top'), 10) +
           parseInt(this.css('margin-bottom'), 10);
  };

  /**
   * Retrieve the border width of the first element in the set of matched
   * elements.
   */
  $.fn.borderWidth = function () {
    return parseInt(this.css('border-left-width'), 10) +
           parseInt(this.css('border-right-width'), 10);
  };

  /**
   * Retrieve the border height of the first element in the set of matched
   * elements.
   */
  $.fn.borderHeight = function () {
    return parseInt(this.css('border-top-width'), 10) +
           parseInt(this.css('border-bottom-width'), 10);
  };

  /**
   * Retrieve the padding width of the first element in the set of matched
   * elements.
   */
  $.fn.paddingWidth = function () {
    return parseInt(this.css('padding-left'), 10) +
           parseInt(this.css('padding-right'), 10);
  };

  /**
   * Retrieve the padding height of the first element in the set of matched
   * elements.
   */
  $.fn.paddingHeight = function () {
    return parseInt(this.css('padding-top'), 10) +
           parseInt(this.css('padding-bottom'));
  };

} (jQuery));
