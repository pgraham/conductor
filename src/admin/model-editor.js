var ${model}_editor = function () {
  var that, elm, btns, grid, cols;

  cols = ${json:columns};

  that = CDT.modelCollectionGrid({
    cols        : cols,
    crudService : window.${crudService},
    idProperty  : '${idProperty}',
    dataitem    : typeof ${model} === 'function' ? ${model}() : {},
    buttons     : {
      "New" : function () {
        ${model}_form(null).on('close', function () {
          that.refresh();
        }).show();
      },
      "Edit" : function () {
        var model = that.getSelected().pop();

        if (model !== undefined) {
          ${model}_form(model).on('close', function () {
            that.refresh();
          }).show();
        }
      },
      "Delete" : function () {
        var models = that.getSelected();

        if (models.length > 0) {
          ${model}_delete(models).show(function () {
            that.refresh();
          });
        }
      }
    }
  });

  /*
  btns = $('<div/>');
  ${each:buttons AS button}
  
    btns.append(
      $('<button/>')
        .attr('type', 'button')
        .text(${button[lbl]})
        .click(${button[fn]}));
  ${done}
  */

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
