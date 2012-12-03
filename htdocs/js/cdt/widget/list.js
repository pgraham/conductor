CDT.ns('CDT.widget');

/**
 * CDT.widget.list
 */
(function ($, CDT, undefined) {
  "use strict";

  function createBasicRenderer(dataIndex) {
    return function (rowData) {
      return rowData[dataIndex];
    }
  }

  // Add a layout function that fills available horizontal space with a lists
  // specified auto-expand columns
  $.layouts.listLayout = function (list) {
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

  // Add a layout that sets the table data elements of each row to line up with
  // their corresponding header elements.
  $.layouts.listRowLayout = function (row) {
    var tbl = row.closest('table'),
        hdrs = tbl.find('thead th');

    row.children().each(function (idx) {
      $(this).outerWidth(hdrs.eq(idx).outerWidth(true), true);
    });
  };

  /**
   * Create a new list widget
   */
  CDT.widget.list = function () {
    var elm, tbl, thead, tbody, headers, selAll, cols = [];

    elm = $('<div/>').addClass('cdt-list');
    tbl = $('<table/>').appendTo(elm);
    thead = $('<thead/>').addClass('ui-widget-content').appendTo(tbl);
    tbody = $('<tbody/>').addClass('ui-widget-content').appendTo(tbl);
    headers = $('<tr/>').appendTo(thead);

    function addColumn(lbl, renderer, autoExpand) {
      var config, hdr;
      config = normalizeAddColumnArgs(lbl, renderer, autoExpand);

      if (config.dataIdx && !config.renderer) {
        config.renderer = createBasicRenderer(config.dataIdx);
      }
      cols.push(config);

      hdr = $('<th/>')
        .addClass('ui-widget-header')
        .addClass(config.autoExpand ? 'auto-expand' : '')
        .append(config.lbl)
        .appendTo(headers);

      if (config.align) {
        CDT.util.align(hdr, config.align);
      }

      // NOTE: autoExpand will override width
      if (config.width) {
        hdr.width(config.width);
      }

      return this;
    }

    function addColumns(cols) {
      $.each(cols, function () {
        addColumn(this);
      });
      return this;
    }

    function addRow(rowData) {
      var row = $('<tr/>').layout('listRowLayout').appendTo(tbody);

      $('<input type="checkbox"/>')
        .addClass('cdt-list-row-selector')
        .data('row-attributes', rowData)
        .click(function () {
          selAll.prop(
            'checked',
            tbody.find('.cdt-list-row-selector:not(:checked)').length ===  0
          );

          elm.trigger('selection-change');
        })
        .appendTo(
          $('<td/>').addClass('ui-widget-content left-col').appendTo(row)
        );

      $.each(cols, function (idx, col) {
        var cell, ctnt;
        
        ctnt = col.renderer(rowData);
        cell = $('<td/>')
          .addClass('ui-widget-content')
          .appendTo(row);

        if ($.isArray(ctnt)) {
          $.each(ctnt, function (idx, item) {
            cell.append(item);
          });
        } else {
          cell.append(ctnt);
        }

        if (col.align) {
          CDT.util.align(cell, col.align);
        }
      });
      row.layout();
    }

    function clearSelected() {
      setAllSelected(false);
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

      elm.layout().trigger('load');
    }

    function selectAll() {
      setAllSelected(true);
    }

    function setAllSelected(selected) {
      tbody.find('.cdt-list-row-selector').prop('checked', selected);
      elm.trigger('selection-change');
    }

    selAll = $('<input type="checkbox"/>').click(function () {
      setAllSelected($(this).is(':checked'));
    });
    headers.append($('<th/>').addClass('ui-widget-header left-col').append(selAll));

    // Apply list layout
    elm.layout('listLayout');

    // TODO Instead of extending elm either the methods need to attached as data
    //      or the jQuery prototype needs to be expanded with list functions
    return $.extend(elm, {
      addColumn: addColumn,
      addColumns: addColumns,
      clearSelected: clearSelected,
      getSelected: getSelected,
      populate: populate,
      selectAll: selectAll
    });
  };

  /*
   * Private function to normalize the arguments given to a lists addColumn
   * method into a config object.
   */
  function normalizeAddColumnArgs(lbl, renderer, autoExpand) {
    var config;
    if ($.isPlainObject(lbl)) {
      return lbl;
    }

    // Old style parameters were passed, use them to create a config object
    config = {
      lbl: lbl,
      autoExpand: autoExpand || false
    };
    
    if (typeof renderer === 'string') {
      config.dataIdx = renderer;
    } else {
      config.renderer = renderer;
    }
    return config;
  }

} (jQuery, CDT));
