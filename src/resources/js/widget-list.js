/**
 * CDT.widget.list
 */
(function ($, CDT, undefined) {
  "use strict";

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  $.layouts.listLayout = function (list) {
    var toFill = list.find('th.auto-expand'),
        width = list.width(),
        allocated = 0,
        remaining,
        fill;

    list.find('th').each(function () {
      if (!$(this).is('.auto-expand')) {
        allocated += $(this).outerWidth(true);
      }
    });

    remaining = width - allocated;
    fill = Math.floor(remaining / toFill.length);
    toFill.each(function () {
      remaining -= fill;
      $(this).outerWidth(fill, true);
    });
    toFill.last().width(toFill.last().width() + remaining);

    list.find('tbody').css('top', list.find('thead').outerHeight(true) + 'px');
  };

  function buildBasicRenderer(dataIndex) {
    return function (rowData) {
      return rowData[dataIndex];
    }
  }

  /**
   * Create a new list widget
   */
  CDT.widget.list = function () {
    var list, elm, tbl, thead, tbody, headers, selAll, cols = [];

    elm = $('<div/>').addClass('cdt-list');
    tbl = $('<table/>').appendTo(elm);
    thead = $('<thead/>').addClass('ui-widget-content').appendTo(tbl);
    tbody = $('<tbody/>').addClass('ui-widget-content').appendTo(tbl);
    headers = $('<tr/>').appendTo(thead);

    function addColumn(lbl, renderer, autoExpand) {
      if (typeof renderer === 'string') {
        renderer = buildBasicRenderer(renderer);
      }
      cols.push(renderer);

      $('<th/>')
        .addClass('ui-widget-header')
        .addClass(autoExpand ? 'auto-expand' : '')
        .append(lbl)
        .appendTo(headers);
    }

    function addRow(rowData) {
      var row = $('<tr/>').appendTo(tbody);

      $('<input type="checkbox"/>')
        .addClass('cdt-list-row-selector')
        .data('row-attributes', rowData)
        .click(function () {
          selAll.prop(
            'checked',
            tbody.find('.cdt-list-row-selector:not(:checked)').length ===  0
          );

          list.fire('selection-change');
        })
        .appendTo(
          $('<td/>').addClass('ui-widget-content left-col').appendTo(row)
        );

      $.each(cols, function (idx, col) {
        row.append(
          $('<td/>')
            .addClass('ui-widget-content')
            .append(col(rowData))
        );
      });

      row.children().each(function (idx) {
        var hdrWidth = headers.children().eq(idx).outerWidth(true)
        $(this).outerWidth(hdrWidth, true);
      });
    }

    function getSelected() {
      return tbody.find('input:checked');
    }

    function populate(data, mapper) {
      tbody.empty();
      selAll.prop('checked', false);

      if ($.isEmptyObject(data)) {
        selAll.prop('disabled', true);
        $('<td/>')
          .attr('colspan', headers.children().length)
          .addClass('no-data')
          .text('No data to display')
          .appendTo( $('<tr/>').appendTo(tbody) )
          .outerWidth(tbody.outerWidth())
          .outerHeight(tbody.outerHeight());
        return;
      }

      selAll.prop('disabled', false);

      $.each(data, function (idx, rowData) {
        if (mapper !== undefined) {
          rowData = mapper(rowData);
        }
        addRow(rowData);
      });

      list.fire('load');
    }

    list = {
      elm: elm,
      addColumn: addColumn,
      getSelected: getSelected,
      populate: populate
    };
    eventuality(list);
  
    selAll = $('<input type="checkbox"/>').click(function () {
      tbody.find('.cdt-list-row-selector')
        .prop('checked', $(this).is(':checked'));

      list.fire('selection-change');
    });
    headers.append($('<th/>').addClass('ui-widget-header left-col').append(selAll));

    // Apply list layout
    elm.layout('listLayout');

    return list;
  };

} (jQuery, CDT));
