function ${model}Editor() {
  this.container = $('<div/>').addClass('cdt-ModelEditor')
    .append(
      $('<div/>').addClass('cdt-ModelEditorBtns')
        .append(
          $('<input type="button" value="New" />').click(function () {
            new ${model}Form(null);
          }))
        .append(
          $('<input type="button" value="Edit" />').click(function () {
            new ${model}Form(this.getSelected().last());
          }))
        .append(
          $('<input type="button" value="Delete" />').click(function () {
            new ${model}Delete(this.getSelected());
          })))
    .append(
      $('<table/>').addClass('cdt-ModelEditorGrid')
        .append(
          $('<tr/>').addClass('cdt-ModelEditorGridHeaders')
            ${each:headers as header}
              .append($('<td/>').text('${header}'))
            ${done}
        ));

  this.retrieve();
}

${model}Editor.prototype = {
  getElement: function () {
    return this.container;
  },

  buildTableRow: function (model) {
    var row = $('<tr/>').addClass('cdt-ModelEditorRow');

    ${each:properties as property}
      row.append($('<td>' + model[${property}] + '</td>'));
    ${done}

    return row;
  },

  retrieve: function () {
    window[models['${model}'].crudService].retrieve(function (data) {

      var count = data.length,
          i;

      for (i = 0; i < count; i++) {
        this.tbl.append(this.buildTableRow(data[i]));
      }
    });
  }
};

function ${model}Delete(selected) {
}
