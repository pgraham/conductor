var ${model}_editor = function () {
  var that, elm, btns, grid, cols, dataitem;

  if (typeof ${model} === 'function') {
    dataitem = ${model}();
  } else {
    dataitem = {};
  }

  cols = [];
  ${each:columns AS column}
    cols.push({
      id: {
        field: '${column[id]}',
        type: '${column[type]}',
        html: true
      },
      lbl: '${column[lbl]}'
    });

    ${if:column[type] = timestamp}
      // Add a wrapper for the field value that converts the time sent from the
      // server from UTC to local.
      if (dataitem['${column[id]}'] === undefined) {
        dataitem['${column[id]}'] = function () {
          var local = Date.utcToLocal(this.options.data['${column[id]}']);
          return local.toString('MMM dS, yyyy @ H:mm');
        };
      } else {
        dataitem['${column[id]}'] = (function (orig) {
          return function () {
            this.options.data['${column[id]}'] = Date.utcToLocal(
              this.options.data['${column[id]}']);
            return orig.call(this);
          };
        }) ( dataitem['${column[id]}'] );
      }
    ${fi}
  ${done}

  grid = CDT.modelSelectionGrid({
    cols     : cols,
    dataitem : dataitem,
    source   : function (request, response) {
      var srvc = window['${crudService}'];

      srvc.retrieve(request, function (data) {
        var i, len;
        for (i = 0, len = data.length; i < len; i += 1) {
          // The grid widget expects the identifier property to be in a
          // property called guid
          data[i].guid = data[i]['${idProperty}'];
        }

        response(data);
      });
    }
  });

  btns = $('<div/>').addClass('cdt-ModelEditorBtns');
  ${each:buttons as button}

    ${if:button = new}
      btns.append(
        $('<button/>')
          .attr('type', 'button')
          .text("New")
          .click(function () {
            ${model}_form(null).on('close', function () {
              grid.refresh();
            }).show();
          })
      );

    ${elseif:button = edit}
      btns.append(
        $('<button/>')
          .attr('type', 'button')
          .text("Edit")
          .click(function () {
            var model = grid.getSelected().pop();

            if (model !== undefined) {
              ${model}_form(model).on('close', function () {
                grid.refresh();
              }).show();
            }
          })
      );

    ${elseif:button = delete}
      btns.append(
        $('<button/>')
          .attr('type', 'button')
          .text("Delete")
          .click(function () {
            var models = grid.getSelected();

            if (models.length > 0) {
              ${model}_delete(models).show(function () {
                grid.refresh();
              });
            }
          })
      );
    ${fi}

  ${done}
  
  elm = $('<div/>')
    .addClass('cdt-ModelEditor')
    .addClass('ui-widget')
    .append( btns )
    .append( grid.elm );

  that = {};
  that.elm = elm;

  that.appendTo = function (jq) {
    elm.appendTo(jq);
  };

  that.refresh = grid.refresh;
  that.getSelected = grid.getSelected;

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
