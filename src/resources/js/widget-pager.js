(function ($, CDT, undefined) {
  "use strict";

  var DEFAULT_PAGE_SIZE  = 10,
      //lblTmpl = stringTemplate('Displaying {0} - {1} of {2}'),
      pagerize;

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  pagerize = function (pager) {
    eventuality(pager);

    pager.getNumPages = function () {
      return Math.ceil(this.total / this.pageSize);
    };

    pager.getPaging = function () {
      return {
        limit: this.pageSize,
        offset: (this.curPage - 1) * this.pageSize
      };
    };

    pager.setTotal = function (t) {
      this.total = t;
      if (this.curPage > this.getNumPages()) {
        this.curPage = this.getNumPages();
        this.fire('page-change');
      }
      this.update();
    };

    pager.update = function () {
      var firstIdx, lastIdx, numPages;

      numPages = this.getNumPages();

      if (this.curPage <= 1) {
        this.prev.button('option', 'disabled', true);
        this.first.button('option', 'disabled', true);
      } else {
        this.prev.button('option', 'disabled', false);
        this.first.button('option', 'disabled', false);
      }

      if (this.curPage >= numPages) {
        this.next.button('option', 'disabled', true);
        this.last.button('option', 'disabled', true);
      } else {
        this.next.button('option', 'disabled', false);
        this.last.button('option', 'disabled', false);
      }

      this.pageNum.val(this.curPage);
      this.numPages.text('of ' + numPages);

      firstIdx = Math.min(((this.curPage - 1) * this.pageSize) + 1, this.total);
      lastIdx = Math.min(firstIdx + this.pageSize - 1, this.total);

      //this.lbl.text(lblTmpl.format([firstIdx, lastIdx, this.total]);
      this.lbl.text('Displaying ' + firstIdx + ' - ' + lastIdx + ' of ' + this.total);
    };
  };

  CDT.widget.pager = function (pageSize) {
    var pager, elm, first, prev, next, last, pageNum, lbl, update,
        curPage = 1, total, numPages;

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

    pager = { 
      elm: elm,
      first: first,
      prev: prev,
      next: next,
      last: last,
      pageNum: pageNum,
      numPages: numPages,
      lbl: lbl,
      pageSize: pageSize || DEFAULT_PAGE_SIZE,
      curPage: 1,
    };
    pagerize(pager);

    first.click(function () {
      pager.curPage = 1;
      pager.update();
      pager.fire('page-change');
    });
    prev.click(function () {
      pager.curPage -= 1;
      pager.update();
      pager.fire('page-change');
    });
    next.click(function () {
      pager.curPage += 1;
      pager.update();
      pager.fire('page-change');
    });
    last.click(function () {
      pager.curPage = pager.getNumPages();
      pager.update();
      pager.fire('page-change');
    });

    pageNum.on('change', function () {
      var changeTo = parseInt(pageNum.val(), 10);

      if (changeTo > pager.getNumPages()) {
        pageNum.val(pager.curPage);
        return;
      }

      pager.curPage = changeTo;
      pager.fire('page-change');
    });

    return pager;
  };

} (jQuery, CDT));
