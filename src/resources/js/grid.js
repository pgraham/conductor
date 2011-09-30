/**
 * Extension to the jQuery UI grid widget that adapts to a CrudServiceProxy.
 *
 * Requires the grid-model files
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, that, undefined) {

  var gridCount = 0, modelGrid, modelCollectionGrid, modelDualGrid,
    gridIdCallback;

  /**
   * This function creates a function that can be passed to a CRUD service
   * retrieve method that will transpose the specified ID property into the
   * property expected by the jQuery-ui grid.
   */
  gridIdCallback = function (response, idProperty) {
    return function (data) {
      for (var i = 0, length = data.length; i < length; i += 1) {
        // The grid widget expects the identifier property to be in a
        // property called guid
        data[i].guid = data[i][idProperty];
      }
      response(data);
    };
  };

  /**
   * This function builds a grid that is populated remotely.
   */
  modelGrid = function (spec) {
    var that, type, grid, head, headers, refresh, cols = [];

    // Define a datastore and dataitem for the grid
    gridCount += 1;
    type = 'modelGrid-data-' + gridCount;

    // Build the grid's structure
    grid = $('<table/>')
      .addClass('cdt-ModelEditorGrid')
      .append(
        $('<thead/>')
          .addClass('cdt-ModelEditorHead')
          .addClass('ui-widget-header'))
      .append(
        $('<tbody/>')
          .addClass('cdt-ModelEditorBody')
          .addClass('ui-widget-content'))
      .append(
        $('<tfoot/>')
          .addClass('cdt-ModelEditorFoot')
          .addClass('ui-widget-content'));

    head = grid.find('thead')
      .append(
        $('<tr/>')
          .addClass('cdt-ModelEditorRow'));

    headers = head.find('tr');
    for (i = 0, len = spec.cols.length; i < len; i++) {
      cols.push(spec.cols[i].id);
      headers.append( $('<th>' + spec.cols[i].lbl + '</th>') );
    }

    grid.grid({
      source: spec.source,
      columns : cols
    });

    that = {
      elm  : grid,
      type : type
    };

    refresh = function () {
      $.ui.datasource.types[type].get( $.ui.datastore.main );
    };
    that.refresh = refresh;

    return that;
  };

  /**
   * This function curries a selection column onto a model grid with the given
   * spec.
   */
  modelSelectionGrid = function (spec) {
    var that, cols, dataitem, getSelected;

    dataitem = spec.dataitem || {};
    dataitem.selected = function () {
      return '<input type="checkbox" value="' + this.get('guid') + '"/>';
    }
    spec.dataitem = dataitem;

    cols = [
      {
        id: {
          field: 'selected',
          html:  true
        },
        lbl: '&nbsp;'
      }
    ].concat(spec.cols);
    spec.cols = cols;

    that = modelGrid(spec);

    getSelected = function () {
      var sel   = that.elm.find('input:checkbox:checked'),
          models = [];

      sel.each(function (idx, elm) {
        var id    = parseInt($(this).val()),
            model = $.ui.datastore.main.get(that.type, id);

        models.push(model.options.data);
      });
      return models;
    };
    that.getSelected = getSelected;

    return that;
  };

  /**
   * This function builds a grid for editing a collection of models.
   * It includes a set of buttons for manipulating the collection and
   * a grid with a selection column.
   */
  modelCollectionGrid = function (spec) {
    var that, elm, btns, grid, appendTo;
    
    grid = modelSelectionGrid({
      cols        : spec.cols,
      dataitem    : spec.dataitem,
      source      : function (request, response) {
        spec.crudService.retrieve(request,
          gridIdCallback(response, spec.idProperty));
      }
    });

    elm = $('<div/>')
      .addClass('cdt-ModelEditor')
      .addClass('ui-widget')
      .append(
        $('<div/>')
          .addClass('cdt-ModelEditorBtns'))
      .append(grid.elm);

    btns = elm.find('.cdt-ModelEditorBtns');
    $.each(spec.buttons, function (index, item) {
      btns.append(
        $('<button/>')
          .attr('type', 'button')
          .text(index)
          .click(item));
    });

    that = {
      elm : elm
    };

    appendTo = function (jq) {
      elm.appendTo(jq);
      return this;
    };
    that.appendTo = appendTo;

    that.refresh = grid.refresh;
    that.getSelected = grid.getSelected;

    return that;
  };

  modelDualGrid = function (spec) {
    var that, elm, available, selected;

    spec.selected = spec.selected || [];

    selected = modelSelectionGrid({
      cols     : [ { id: spec.nameProperty, lbl: 'Selected' } ],
      dataitem : spec.dataitem,
      source   : spec.selected
    });

    available = modelSelectionGrid({
      cols     : [ { id: spec.nameProperty, lbl: 'Available' } ],
      dataitem : spec.dataitem,
      source   : spec.available
    });

    ctrls = $('<div/>')
      .addClass('cdt-ModelDualGridControls')
      .append(
        $('<button/>')
          .attr('type', 'button')
          .text('Add')
          .click(function () {
            var sel = available.getSelected();

            $.ui.datastore.main.remove(available.type, sel);
            $.ui.datastore.main.add(selected.type, sel);
          }))
      .append(
        $('<button/>')
          .attr('type', 'button')
          .text('Remove')
          .click(function () {
            var sel = selected.getSelected();

            $.ui.datastore.main.remove(selected.type, sel);
            $.ui.datastore.main.add(available.type, sel);
          }));

    elm = $('<div/>')
      .addClass('cdt-ModelDualGrid')
      .append(available.elm)
      .append(ctrls)
      .append(selected.elm);

    that = {
      elm       : elm,
      available : available,
      selected  : selected
    };

    that.getSelected = function () {
      return selected.getSelected();
    };

    return that;
  };

  // Expose parts
  that.modelGrid = modelGrid;
  that.modelSelectionGrid = modelSelectionGrid;
  that.modelCollectionGrid = modelCollectionGrid;
  that.modelDualGrid = modelDualGrid;
  that.gridIdCallback = gridIdCallback;

} (jQuery, CDT));
