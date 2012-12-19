/**
 * Composite widget composed of a toolbar, list and pager.  This widget will
 * handle most of the plumbing required for this common combination of widgets.
 */
"use strict";
(function (exports, $, CDT, undefined) {

  var defaultOpts = { pageSize: 10 };

  exports.pagedActionList = function (opts) {
    var elm, actions, list, pager, store, selectionActions = [];

    function addButton(cfg) {
      actions.toolbar('addButton', cfg);

      if (cfg.requiresSelection) {
        actions.toolbar('disable', cfg.label);
        selectionActions.push(cfg.label);
      }
    }

    function loadList() {
      store.load({
        page: pager.getPaging(),
        sort: opts.defaultSort || { field: 'id', dir: 'asc' },
        filter: opts.baseFilter || {}
      });
    }

    opts = verifyOpts(opts);
    store = opts.store;

    actions = CDT.widget.toolbar();
    $.each(opts.actions, function () {
      addButton(this);
    });

    pager = CDT.widget.pager(opts.pageSize).on('page-change', loadList);

    list = CDT.widget.list({
      columns: opts.columns
    });

    list.on('listselectionchange', function (e, data) {
      $.each(selectionActions, function () {
        actions.toolbar('setEnabled', this, !!data.selected.length);
      });
    });

    store.on('beforeload', function (e) {
      list.find('tbody').working();
    });
    store.on('load', function (e, data) {
      list.list('setRows', data.items);
      pager.setTotal(data.total);
      list.find('tbody').done();

      list.find('tbody tr:not(.no-data)').hover(
        function () { $(this).addClass('cdt-list-row-over'); },
        function ( ){ $(this).removeClass('cdt-list-row-over'); }
      );

      if (opts.onRowClick) {
        list.find('tbody tr:not(.no-data)').click(function (e) {
          var data = $(this).find('.cdt-list-row-selector')
            .data('row-attributes');

          opts.onRowClick.apply(this, [ e, data ]);
        });
      }
    });
    loadList();

    elm = $('<div class="cdt-paged-action-list"/>')
      .addClass('ui-widget')
      .append(actions, list, pager)
      .layout('fillWith', '.cdt-list');

    return {
      elm: elm,
      actions: actions,
      list: list,
      pager: pager,
      loadList: loadList
    };
  };

  /* Helper function to merge options with defaults and verify the result. */
  function verifyOpts(opts) {
    opts = $.extend({}, defaultOpts, opts);
    if (!opts.store) {
      throw new Error('PagedActionList requires a store');
    }
    if (!opts.columns) {
      throw new Error('PagedActionList requires columns');
    }
    return opts;
  }

} (CDT.ns('CDT.widget'), jQuery, CDT));
