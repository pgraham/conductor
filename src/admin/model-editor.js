var ${model}_editor = function () {
  var that, elm, btns, grid, cols, source, selected, refresh;

  cols = []; 
  ${each:columns AS column}
    cols.push({
      property: '${column[id]}',
      label: '${column[lbl]}'
    });
  ${done}

  source = $.ui.dataview({
    source: function (request, gridCb) {
      window['${crudService}'].retrieve(request, function (response) {
        var i, len, data;

        data = response.data;
        for (i = 0, len = data.length; i < len; i += 1) {
          ${each:columns AS column}
            ${if:column[type] = timestamp}
              // Add a wrapper for the field value that converts the time sent
              // from the server from UTC to local.
              data[i]['${column[id]}'] =
                Date
                  .utcToLocal(data[i]['${column[id]}'])
                  .toString('MMM dS, yyyy @ H:mm');
            ${fi}
          ${done}
        }

        gridCb(data, response.total);
      });
    }
  });

  refresh = function () {
    source.refresh();
    selected = [];
  };
  refresh();

  grid = dom.table().addClass('cdt-ModelEditorGrid');
  grid.find('thead').addClass('ui-widget-header');
  grid.find('tbody').addClass('ui-widget-content');
  grid.find('tfoot').addClass('ui-widget-content');

  btns = $('<div/>').addClass('cdt-ModelEditorBtns');
  ${each:buttons as button}

    ${if:button = new}
      btns.append(dom.button("New", function () {
        ${model}_form(null).on('close', function () {
          refresh();
        }).show();
      }));

    ${elseif:button = edit}
      btns.append(dom.button("Edit", function () {
        var model = selected[selected.length - 1];

        if (model !== undefined) {
          ${model}_form(model).on('close', function () {
            refresh();
          }).show();
        }
      }));

    ${elseif:button = delete}
      btns.append(dom.button("Delete", function () {
        if (selected.length > 0) {
          ${model}_delete(selected).show(function () {
            refresh();
          });
        }
      }));

    ${fi}

  ${done}
  
  elm = $('<div/>')
    .addClass('cdt-ModelEditor')
    .addClass('ui-widget')
    .append( btns )
    .append( grid );

  that = {};

  that.appendTo = function (jq) {
    elm.appendTo(jq);

    // Don't initialize the grid until it has been appended to the document
    // as the jQuery-ui initialization method relies on the element being
    // in the DOM.
    grid.grid({
      columns: cols,
      source: source.result
    }).gridSelectable({
      selected: selected
    });
  };

  that.refresh = refresh;
  that.getSelected = function () {
    return selected;
  };

  return that;
}

var ${model}_delete = function (models) {
  var that, elm, title, msg, show;

  if (models.length === 1) {
    title = 'Delete ${singular}?';
    msg = 'Are you sure you want to delete the selected ${singular}?';
  } else {
    title = 'Delete ${plural}?';
    msg = 'Are you sure the want to delete the ' + models.length +
      ' selected ${plural}?';
  }

  elm = $('<div/>')
    .attr('title', title)
    .append($('<p><span class="ui-icon ui-icon-alert" ' +
      'style="float:left;margin:0 7px 20px 0;"></span>' + msg + '</p>'));

  that = {};

  show = function (deleteCb) {
    elm.dialog({
      resizable: false,
      height: 140,
      modal: true,
      buttons: {
        "Delete" : function () {
          var i, len, toDelete = [];
          for (i = 0, len = models.length; i < len; i++) {
            toDelete.push(models[i].${idProperty});
          }

          elm.dialog('close');

          window['${crudService}']['delete'](toDelete, deleteCb);
        },
        "Cancel" : function () {
          elm.dialog('close');
        }
      }
    });
  };
  that.show = show;

  return that;
};
