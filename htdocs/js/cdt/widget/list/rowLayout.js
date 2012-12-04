(function ($, undefined) {

  /**
   * Register a layout function which sizes table cells to match the width of
   * their corresponding header.
   */
  $.layouts.listRowLayout = function (row) {
    var tbl = row.closest('table'),
        hdrs = tbl.find('thead th');

    row.children().each(function (idx) {
      $(this).outerWidth(hdrs.eq(idx).outerWidth(true), true);
    });
  };

} (jQuery));
