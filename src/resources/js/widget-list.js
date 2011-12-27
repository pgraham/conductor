(function ($, CDT, undefined) {
  "use strict";

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  CDT.widget.list = function () {
    var list, elm, thead, cols, tbody;

    cols = [];
    thead = $('<thead/>');
    tbody = $('<tbody/>');
    elm = $('<div/>')
      .addClass('cdt-list')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all')
      .append($('<table/>')
        .append(thead.append($('<tr/>')))
        .append(tbody)
      );
  
    thead.find('tr').append(
      $('<th/>').addClass('ui-widget-header').text(' ')
    );

    list = { elm: elm };
    eventuality(list);

    list.addColumn = function (lbl, renderer) {
      var dataIndex;
      if (typeof renderer === 'string') {
        dataIndex = renderer;
        renderer = function (rowData) {
          return rowData[dataIndex];
        };
      }
      cols.push(renderer);

      thead.find('tr').append(
        $('<th/>').addClass('ui-widget-header').text(lbl)
      );
    };

    list.addRow = function (rowData) {
      var chk, row;

      row = $('<tr/>');

      chk = $('<input type="checkbox"/>')
        .data('row-attributes', rowData)
        .click(function () {
          list.fire('selection-change');
        });
      row.append($('<td/>')
        .addClass('ui-widget-content')
        .append(chk)
      );

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

      $.each(data, function (idx, rowData) {
        if (mapper !== undefined) {
          rowData = mapper(rowData);
        }
        list.addRow(rowData);
      });
    };

    return list;
  };

} (jQuery, CDT));
