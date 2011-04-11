var ${model}_editor = function () {
  var that, cols = ${json:columns};

  that = editor({
    type : '${model}',
    cols : cols,
    form : ${model}_form,
    del  : ${model}_delete
  });

  return that;
}

$.ui.dataitem.extend('${model}', {
  selected: function () {
    return '<input type="checkbox" name="${model}_sel[]"' +
       ' value="' + this.get('id') + '" class="${model}_check"/>';
  }
});

$.ui.datasource({
  type: '${model}',
  source: function (request, response) {
    window.${crudService}.retrieve(function (data) {
      for (var i = 0, length = data.length; i < length; i++) {
        // The grid widget expects the identifier property to be in a
        // property called guid
        data[i].guid = data[i].${idProperty};
      }
      response(data);
    });
  }
});

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

          window['${crudService}'].delete(toDelete, deleteCb);
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
