
function ${model}Editor() {
  this.container = $('<div/>');
  this.tbl       = $('<table/>').addClass('cdt-ModelEditor');

  var newBtn  = $('<input type="button" value="New" />')
        .click(function () {
          console.log('Create new edit form');
        }),
      editBtn = $('<input type="button" value="Edit" />')
        .click(function () {
          console.log('Create new edit form');
        }),
      delBtn  = $('<input type="button" value="Delete" />')
        .click(function () {
          console.log('Prompt for delete');
        }),
      btns = $('<div/>')
        .append(newBtn)
        .append(editBtn)
        .append(delBtn);
  this.container.append(btns);

  var headers = $('<tr>').addClass('cdt-Header');
  ${each:headers as header}
    headers.append($('<td>${header}</td>'));
  ${done}
  this.tbl.append(headers);

  this.container.append($('<div>Manager ${pluralName}</div>'));
  this.container.append(tbl);

  BaseView.call(this, this.container.get(0));
}

${model}Editor.prototype = $.extend({}, new BaseView(), {

  buildTableRow: function (model) {
    var row = $('<tr/>').addClass('cdt-Row');

    ${each:properties as property}
      row.append($('<td>' + model[${property}] + '</td>'));
    ${done}

    return row;
  },

  retrieve: function () {
    window['${serviceVar}'].retrieve(function (data) {
      console.log(data);

      var count = data.length,
          i;

      for (i = 0; i < count; i++) {
        this.tbl.append(this.buildTable(data[i]));
      }
    });
  }
});
