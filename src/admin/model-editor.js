function ${model}Editor() {
  var that = this;

  this.rows = $('<tbody/>')
    .addClass('cdt-ModelEditorBody')
    .addClass('ui-widget-content');

  this.grid = $('<table/>')
    .addClass('cdt-ModelEditorGrid')
    .append(
      $('<thead/>')
        .addClass('cdt-ModelEditorHead')
        .addClass('ui-widget-header')
        .append(
          $('<tr/>')
            .addClass('cdt-ModelEditorRow')
            .append($('<th>&nbsp;</th>'))
            ${each:headers as header}
              .append($('<th/>').text('${header}'))
            ${done}
        )
    )
    .append(
      $('<tbody/>')
        .addClass('cdt-ModelEditorBody')
        .addClass('ui-widget-content')
    );

  this.container = $('<div/>')
    .addClass('cdt-ModelEditor')
    .addClass('ui-widget')
    .append(
      $('<div/>').addClass('cdt-ModelEditorBtns')
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('New')
            .click(function () {
              new ${model}Form(that, null);
            })
        )
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('Edit')
            .click(function () {
              var sel   = that.getSelected(),
                  id    = sel.length > 0
                    ? parseInt(sel.last().val())
                    : null,
                  model = id !== null
                    ? $.ui.datastore.main.get('${model}', id)
                    : null;

              if (model !== null) {
                new ${model}Form(that, model.options.data);
              }
            })
        )
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('Delete')
            .click(function () {
              var sel    = that.getSelected(),
                  models = [];

              if (sel.length == 0) {
                return;
              }

              sel.each(function (idx, elm) {
                var id = parseInt($(this).val()),
                    model = $.ui.datastore.main.get('${model}', id);

                models.push(model.options.data);
              });

              new ${model}Delete(that, models);
            })
        )
    )
    .append(this.grid);

  $.ui.dataitem.extend('${model}', {
    selected: function () {
      return '<input type="checkbox" name="${model}_sel[]"' +
         ' value="' + this.get('id') + '" class="${model}_check"/>';
    }
  });

  $.ui.datasource({
    type: '${model}',
    source: function (request, response) {
      window['${crudService}'].retrieve(function (data) {
        for (var i = 0, length = data.length; i < length; i++) {
          // The grid widget expects the identifier property to be in a
          // property called guid
          data[i].guid = data[i].${id_prop};
        }
        response(data);
      });
    }
  });
  this.grid.grid({
    type: '${model}',
    columns: [ {
      field: 'selected',
      html:  true
    } ].concat([ ${join:propertyStrs:,} ])
  });
}

${model}Editor.prototype = {
  getElement: function () {
    return this.container;
  },

  getSelected: function () {
    var sel = $(this.container).find('input.${model}_check:checked');
    return sel;
  },

  refresh: function () {
    $.ui.datasource.types['${model}'].get( $.ui.datastore.main );
  }
};

function ${model}Delete(editor, selected) {
  var title = selected.length == 1
        ? models['${model}'].name.singular
        : models['${model}'].name.plural,
      msg = selected.length == 1
        ? 'Delete ' + models['${model}'].name.singular
        : 'Delete ' + selected.length + ' ' + models['${model}'].name.plural,
      that = this;

  this.editor = editor;
  this.dialog = $('<div/>')
    .attr('title', title)
    .append($('<p><span class="ui-icon ui-icon-alert" ' +
      'style="float:left;margin:0 7px 20px 0;"></span>' + msg + '</p>'));

  this.dialog.dialog({
    resizable: false,
    height: 140,
    modal: true,
    buttons: {
      "Delete": function () {
        var i, length, toDelete = [];
        for (i = 0, length = selected.length; i < length; i++) {
          toDelete.push(selected[i].${id_prop});
        }

        $(this).dialog("close");

        window['${crudService}'].delete(toDelete, function () {
          that.editor.refresh();
        });
      },
      "Cancel": function () {
        $(this).dialog("close");
      }
    }
  });

  // TODO Build a confirmation dialog for the models.
}
