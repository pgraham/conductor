(function ($, CDT, undefined) {
  "use strict";

  var DEFAULT_PAGE_SIZE  = 10;

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  CDT.widget.pager = function (pageSize) {
    var pager, elm, first, prev, next, last, pageNum, lbl, update,
        curPage = 1, total, numPages;

    pageSize = pageSize || DEFAULT_PAGE_SIZE;

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
    lbl = $('<span/>')
      .css('float', 'right');

    pageNum = $('<input type="text"/>');

    elm = $('<div class="cdt-pager"/>')
      .addClass('ui-widget-content')
      .append(first)
      .append(prev)
      .append($('<span/>').addClass('page-lbl').text('Page:'))
      .append(pageNum)
      .append(next)
      .append(last)
      .append(lbl);

    pager = { elm: elm };
    eventuality(pager);

    first.click(function () {
      curPage = 1;
      update();
      pager.fire('page-change');
    });
    prev.click(function () {
      curPage -= 1;
      update();
      pager.fire('page-change');
    });
    next.click(function () {
      curPage += 1;
      update();
      pager.fire('page-change');
    });
    last.click(function () {
      curPage = Math.ceil(total / pageSize);
      update();
      pager.fire('page-change');
    });

    update = function () {
      var firstIdx, lastIdx;

      if (curPage <= 1) {
        prev.button('option', 'disabled', true);
        first.button('option', 'disabled', true);
      } else {
        prev.button('option', 'disabled', false);
        first.button('option', 'disabled', false);
      }

      if (curPage >= numPages) {
        next.button('option', 'disabled', true);
        last.button('option', 'disabled', true);
      } else {
        next.button('option', 'disabled', false);
        last.button('option', 'disabled', false);
      }

      pageNum.val(curPage);

      firstIdx = Math.min(((curPage - 1) * pageSize) + 1, total);
      lastIdx = Math.min(firstIdx + pageSize - 1, total);

      lbl.text('Displaying ' + firstIdx + ' - ' + lastIdx + ' of ' + total);
    };

    pageNum.on('change', function () {
      var changeTo = parseInt(pageNum.val(), 10);

      if (changeTo > numPages) {
        pageNum.val(curPage);
        return;
      }

      curPage = changeTo;
      pager.fire('page-change');
    });

    pager.getPaging = function () {
      return {
        limit: pageSize,
        offset: (curPage - 1) * pageSize
      };
    };

    pager.setTotal = function (t) {
      total = t;
      numPages = Math.ceil(total / pageSize);
      update();
    };

    return pager;
  };

} (jQuery, CDT));
