CDT.ns('CDT.widget');

(function ($, CDT, undefined) {
  "use strict";

  var DEFAULT_PAGE_SIZE  = 10;
      //lblTmpl = stringTemplate('Displaying {0} - {1} of {2}');

  CDT.widget.pager = function (pageSize) {
    var elm, first, prev, next, last, pageNum, numPages, lbl,
        curPage = 1,
        total;

    pageSize = pageSize || DEFAULT_PAGE_SIZE;

    function getNumPages() {
      return Math.ceil(total / pageSize);
    }

    function update() {
      var firstIdx, lastIdx, pages;

      pages = getNumPages();

      if (curPage === 0 && pages > 0) {
        // This can happen when an initially empty list has something added to
        // it.  In this case, move to the first page.
        curPage = 1;
      }

      if (curPage <= 1) {
        prev.button('option', 'disabled', true);
        first.button('option', 'disabled', true);
      } else {
        prev.button('option', 'disabled', false);
        first.button('option', 'disabled', false);
      }

      if (curPage >= pages) {
        next.button('option', 'disabled', true);
        last.button('option', 'disabled', true);
      } else {
        next.button('option', 'disabled', false);
        last.button('option', 'disabled', false);
      }

      pageNum.val(curPage);
      numPages.text('of ' + pages);

      firstIdx = Math.max(0,
        Math.min(((curPage - 1) * pageSize) + 1, total));
      lastIdx = Math.min(firstIdx + pageSize - 1, total);

      //this.lbl.text(lblTmpl.format([firstIdx, lastIdx, this.total]));
      lbl.text('Displaying ' + firstIdx + ' - ' + lastIdx + ' of ' + total);
    }

    first = $('<button/>').text('<<').button({
      icons: { primary: 'ui-icon-arrowthickstop-1-w' },
      text: false
    });
    prev = $('<button/>').text('<').button({
      icons: { primary: 'ui-icon-arrowthick-1-w' },
      text: false
    });
    next = $('<button/>').text('>').button({
      icons: { primary: 'ui-icon-arrowthick-1-e' },
      text: false
    });
    last = $('<button/>').text('>>').button({
      icons: { primary: 'ui-icon-arrowthickstop-1-e' },
      text: false
    });

    pageNum = $('<input type="text" class="current-page ui-corner-all" />');
    numPages = $('<span class="num-pages" />');

    lbl = $('<span/>')
      .css('float', 'right');

    elm = $('<div class="cdt-pager"/>')
      .addClass('ui-widget-content')
      .append(first)
      .append(prev)
      .append($('<span/>').addClass('page-lbl').text('Page:'))
      .append(pageNum)
      .append(numPages)
      .append(next)
      .append(last)
      .append(lbl);

    first.click(function () {
      curPage = 1;
      update();
      elm.trigger('page-change');
    });
    prev.click(function () {
      curPage -= 1;
      update();
      elm.trigger('page-change');
    });
    next.click(function () {
      curPage += 1;
      update();
      elm.trigger('page-change');
    });
    last.click(function () {
      curPage = getNumPages();
      update();
      elm.trigger('page-change');
    });

    pageNum.on('change', function () {
      var changeTo = parseInt(pageNum.val(), 10);

      if (changeTo > getNumPages()) {
        pageNum.val(curPage);
        return;
      }

      curPage = changeTo;
      elm.trigger('page-change');
    });

    return $.extend(elm, {
      getNumPages: getNumPages,
      getPaging: function () {
        return {
          limit: pageSize,
          offset: Math.max((curPage - 1) * pageSize, 0)
        };
      },
      setTotal: function (t) {
        total = t;

        if (total === 0) {
          curPage = 0;
        } else if (curPage > getNumPages()) {
          curPage = getNumPages();
          elm.trigger('page-change');
        }
        update();
      },
      update: update
    });
  };

} (jQuery, CDT));
