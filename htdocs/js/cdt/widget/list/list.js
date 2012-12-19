/**
 * CDT.widget.list
 */
(function ($, CDT, exports, undefined) {
  "use strict";

  function createBasicRenderer(dataIndex) {
    return function (rowData) {
      return rowData[dataIndex];
    }
  }

  $.widget('ui.list', {
    options: {
      columns: [],
      rows: []
    },

    _create: function () {
      var self = this;
      this.element.addClass('cdt-list ui-widget-content');

      this.tbl = $('<table/>').appendTo(this.element).layout('columnLayout');
      this.thead = $('<thead/>').appendTo(this.tbl);
      this.tbody = $('<tbody/>').appendTo(this.tbl);
      this.headers = $('<tr/>').appendTo(this.thead);

      this.selAll = $('<input type="checkbox"/>').click(function (e) {
        self._setAllSelected($(this).is(':checked'));
        self._trigger('selectionchange', e, { selected: self.getSelected() });
      });
      this.headers.append(
        $('<th/>').addClass('ui-widget-header left-col').append(this.selAll)
      );

      this.cols = [];

      if (this.options.columns.length > 0) {
        this.addColumns(this.options.columns);
      }

      if (this.options.rows.length > 0) {
        this.setRows(this.options.rows);
      }
    },

    _setOption: function (key, value) {
      // TODO
    },

    _destroy: function () {
      // TODO
    },

    addColumn: function (col) {
      var config = $.extend({}, col);
      if (config.dataIdx && !config.renderer) {
        config.renderer = createBasicRenderer(config.dataIdx);
      }

      this.cols.push(config);
      this._addHeader(config);

      return this;
    },

    addColumns: function (cols) {
      var self = this;
      $.each(cols, function () {
        self.addColumn(this);
      });
      return this;
    },

    addRow: function (rowData) {
      var self = this, row;

      this.tbody.find('tr.no-data').remove();
      
      row = $('<tr/>')
        .append(this._buildRowSelect(rowData))
        .layout('listRowLayout')
        .appendTo(this.tbody);

      $.each(this.cols, function (idx, col) {
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
      return this;
    },

    clearSelected: function () {
      this._setAllSelected(false);
      this.selAll.prop('checked', false);
      this._trigger('selectionchange', null, { selected: [] });
    },

    getSelected: function () {
      var data = [];
      $.each(this.tbody.find('input:checked'), function (idx, selected) {
        data.push($(this).data('row-attributes'));
      });
      return data;
    },

    setRows: function (rows, mapper) {
      var self = this;
      this.tbody.empty();
      this.selAll.prop('checked', false);

      if ($.isEmptyObject(rows)) {
        this.selAll.prop('disabled', true);
        $('<td/>')
          .attr('colspan', this.headers.children().length)
          .text('No data to display')
          .appendTo( $('<tr class="no-data"/>').appendTo(this.tbody) )
          .outerWidth(this.tbody.outerWidth())
          .outerHeight(this.tbody.outerHeight());
        return;
      }

      this.selAll.prop('disabled', false);

      $.each(rows, function (idx, row) {
        if (mapper) {
          row = mapper(row);
        }
        self.addRow(row);
      });

      this.element.layout();

      this._trigger('selectionchange', null, { selected: [] });
      return this;
    },

    selectAll: function () {
      this._setAllSelected(true);
      this.selAll.prop('checked', true);
      this._trigger('selectionchange', null, { selected: this.getSelected() });
    },

    _buildRowSelect: function (rowData) {
      var self = this, check;

      check = $('<input type="checkbox"/>')
        .addClass('cdt-list-row-selector')
        .data('row-attributes', rowData)
        .click(function (e) {
          e.stopPropagation();
          self._updateSelAll();
          self._trigger('selectionchange', e, { selected: self.getSelected() });
        })

      return $('<td/>')
        .addClass('ui-widget-content left-col')
        .append(check)
        .click(function (e) {
          e.stopPropagation();
          check.prop('checked', !check.prop('checked'));
          self._updateSelAll();
          self._trigger('selectionchange', e, { selected: self.getSelected() });
        });
    },

    _setAllSelected: function (selected) {
      this.tbody.find('.cdt-list-row-selector').prop('checked', selected);
    },

    _addHeader: function (config) {
      var hdr = $('<th/>')
        .addClass('ui-widget-header')
        .addClass(config.autoExpand ? 'auto-expand' : '')
        .append(config.lbl)
        .appendTo(this.headers);

      if (config.align) {
        CDT.util.align(hdr, config.align);
      }

      // NOTE: autoExpand will override width
      if (config.width) {
        hdr.width(config.width);
      }
    },

    _updateSelAll: function () {
      this.selAll.prop('checked',
        this.tbody.find('.cdt-list-row-selector:not(:checked)').length ===  0
      );
    }

  });

  /**
   * Create a new list widget
   */
  exports.list = function (cfg) {
    return $('<div/>').list(cfg);
  };

} (jQuery, CDT, CDT.ns('CDT.widget')));
