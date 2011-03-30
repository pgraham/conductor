function ${model}Editor() {
  this.rows = $('<tbody/>')
    .addClass('cdt-ModelEditorBody')
    .addClass('ui-widget-content');

  this.container = $('<div/>')
    .addClass('cdt-ModelEditor')
    .addClass('ui-widget')
    .append(
      $('<div/>').addClass('cdt-ModelEditorBtns')
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('New')
            .data('parent', this)
            .click(function () {
              new ${model}Form($(this).data('parent'), null);
            }))
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('Edit')
            .click(function () {
              new ${model}Form(this.getSelected().last());
            }))
        .append(
          $('<button />')
            .attr('type', 'button')
            .text('Delete')
            .click(function () {
              new ${model}Delete(this.getSelected());
            })))
    .append(
      $('<table/>')
        .addClass('cdt-ModelEditorGrid')
        .append(
          $('<thead/>')
            .addClass('cdt-ModelEditorHead')
            .addClass('ui-widget-header')
            .append(
              $('<tr/>')
                .addClass('cdt-ModelEditorRow')
                ${each:headers as header}
                  .append($('<th/>').text('${header}'))
                ${done}
            ))
        .append(this.rows));

  this.retrieve();
}

${model}Editor.prototype = {
  getElement: function () {
    return this.container;
  },

  buildTableRow: function (model) {
    var row = $('<tr/>').addClass('cdt-ModelEditorRow');

    ${each:properties as property}
      row.append($('<td>' + model.${property} + '</td>'));
    ${done}

    return row;
  },

  retrieve: function () {
    this.rows.empty();
    window[models['${model}'].crudService].retrieve((function (editor) {
      return function (data) {
        var count = data.length, i;
        for (i = 0; i < count; i++) {
          editor.rows.append(editor.buildTableRow(data[i]));
        }
      };
    } (this)));
  }
};

function ${model}Delete(selected) {
}
