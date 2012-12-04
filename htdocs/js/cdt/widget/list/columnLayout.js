"use strict";
(function ($, undefined) {

  /**
   * Register a layout function for handling auto expanding list columns.
   */
  $.layouts.columnLayout = function (list) {
    var toFill = list.find('th.auto-expand'),
        hdrs = list.find('th'),
        rows = list.find('tbody tr'),
        width = list.width(),
        allocated = 0,
        remaining,
        fill;

    // Remove any set widths so that columns that have had their content changed
    // since the last layout will get adjusted by the browser
    toFill.width('');
    list.find('tbody td').width('');

    // Determine the amount of width allocated to non auto-expanding columns
    hdrs.each(function () {
      if (!$(this).is('.auto-expand')) {
        allocated += $(this).outerWidth(true);
      }
    });

    // Divide the remaining width amongst the auto-expand columns. Add any
    // remainder due to integer division truncation to the right most
    // auto-expand column
    remaining = width - allocated;
    fill = Math.floor(remaining / toFill.length);
    toFill.each(function () {
      remaining -= fill;
      $(this).outerWidth(fill, true);
    });
    toFill.last().width(toFill.last().width() + remaining);

    list.find('tbody').css('top', list.find('thead').outerHeight(true) + 'px');
  };

} (jQuery));
