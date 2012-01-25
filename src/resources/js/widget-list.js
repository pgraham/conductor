/**
 * CDT.widget.list
 */
(function ($, CDT, undefined) {
  "use strict";

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  /**
   * Create a new list widget
   */
  CDT.widget.list = function () {
    var list, elm, tbl, thead, headers, selAll, cols, tbody;

    cols = [];
    headers = $('<tr/>');
    thead = $('<thead/>').append(headers);
    tbody = $('<tbody/>');
    tbl = $('<table/>').append(thead).append(tbody);

    elm = $('<div/>').addClass('cdt-list').append(tbl);

    list = { elm: elm };
    eventuality(list);
  
    selAll = $('<input type="checkbox"/>')
      .click(function () {
        tbody.find('.cdt-list-row-selector')
          .prop('checked', $(this).is(':checked'));

        list.fire('selection-change');
      });
    headers.append($('<th/>').addClass('ui-widget-header').append(selAll));

    list.addColumn = function (lbl, renderer, autoExpand) {
      var dataIndex, elm;
      if (typeof renderer === 'string') {
        dataIndex = renderer;
        renderer = function (rowData) {
          return rowData[dataIndex];
        };
      }
      cols.push(renderer);

      elm = $('<th/>').addClass('ui-widget-header').append(lbl);
      headers.append(elm);

      if (autoExpand === true) {
        elm.addClass('auto-expand');
      }
    };

    list.addRow = function (rowData) {
      var chk, row;

      row = $('<tr/>');

      chk = $('<input type="checkbox"/>')
        .addClass('cdt-list-row-selector')
        .data('row-attributes', rowData)
        .click(function () {
          selAll.prop(
            'checked',
            tbody.find('.cdt-list-row-selector:not(:checked)').length ===  0
          );

          list.fire('selection-change');
        });

      row.append( $('<td/>').addClass('ui-widget-content').append(chk) );

      $.each(cols, function (idx, col) {
        row.append(
          $('<td/>')
            .addClass('ui-widget-content')
            .append(col(rowData))
        );
      });

      tbody.append(row);
    };

    list.getSelected = function () {
      return tbody.find('input:checked');
    };

    list.populate = function (data, mapper) {
      tbody.empty();
      selAll.prop('checked', false);

      $.each(data, function (idx, rowData) {
        if (mapper !== undefined) {
          rowData = mapper(rowData);
        }
        list.addRow(rowData);
      });

      list.fire('load');
    };

    // Register a layout function that fills the table with the auto expand
    // column if specified and a layout that fills the list div with the table
    $(document).ready(function () {
      var listLayout;

      listLayout = function () {
        var toFill = headers.find('th.auto-expand'),
            width = tbl.width(),
            allocated = 0,
            remaining,
            fill;

        headers.children().each(function () {
          if (!$(this).is('.auto-expand')) {
            allocated += $(this).outerWidth(true);
          }
        });

        remaining = width - allocated;
        fill = remaining / toFill.length;
        toFill.each(function () {
          var $this = $(this), margin, border, padding;

          margin = parseInt($this.css('margin-left'), 10) +
                   parseInt($this.css('margin-right'), 10);
          border = parseInt($this.css('border-left-width'), 10) +
                   parseInt($this.css('border-right-width'), 10);
          padding = parseInt($this.css('padding-left'), 10) + 
                    parseInt($this.css('padding-right'), 10);

          $this.width(fill - (margin + border + padding));
        });
      };
      listLayout();
      CDT.layout.register(listLayout);

    });

    return list;
  };

} (jQuery, CDT));
