"use strict";
(function ($, undefined) {

  function ListLayout(list) {
    this.list = list;

    this.head = list.find('thead');
    this.body = list.find('tbody');
  }

  ListLayout.prototype.apply = function () {

    this.autoExpand();

    this.positionBody();

    this.sizeCells();

    this.scrollbarAdjust();
  };

  ListLayout.prototype.autoExpand = function () {
    var toFill = this.list.find('th.auto-expand'),
        hdrs = this.list.find('th'),
        allocated = 0,
        remaining,
        fill;

    // Remove any set widths so that columns that have had their content changed
    // since the last layout will get adjusted by the browser
    toFill.width('');
    //list.find('tbody td:not(.check-col)').width('');

    // Determine the amount of width allocated to non auto-expanding columns
    hdrs.find(':not(.auto-expand)').each(function () {
      allocated += $(this).outerWidth(true);
    });

    // Divide the remaining width amongst the auto-expand columns.
    remaining = this.list.width() - allocated;
    fill = Math.floor(remaining / toFill.length);
    toFill.each(function () {
      remaining -= fill;
      $(this).outerWidth(fill, true);
    });

    // Add any remainder due to integer division truncation to the right most
    // auto-expand column
    toFill.last().width(function (idx, width) {
      return width + remaining;
    });
  };

  ListLayout.prototype.positionBody = function () {
    this.body.css('top', this.head.outerHeight(true) + 'px');
  };

  ListLayout.prototype.scrollbarAdjust = function () {
    if (!this.body.children().length) {
      return;
    }

    // The thead and tbody may not have the same width due to scroll bar.
    // If not then subtract the difference from the cells in the right most
    // column to ensure tbody cells line up with the thead cells.
    var headW = this.head.children().first().width();
    var bodyW = this.body.children().first().width();
    var diff = headW - bodyW;
    console.log("Head/body width difference: " + diff + " ( " + headW + " - " + bodyW + ")");

    if (diff > 0) {
      this.body.find('td:last-child').width(function (idx, width) {
        return width - diff;
      });
    }
  };

  ListLayout.prototype.sizeCells = function () {
    var hdrs = this.head.find('th');

    this.body.children().each(function () {
      $(this).children().each(function (idx) {
        if ($(this).is('.check-col')) {
          // Check column is sized by CSS
          return;
        }
        $(this).width(hdrs.eq(idx).width());
      });

    });
  };

  /**
   * Register a layout function for handling auto expanding list columns.
   */
  $.layouts.listLayout = function (list) {
    new ListLayout(list).apply();
  };

} (jQuery));
