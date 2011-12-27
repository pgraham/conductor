(function ($, CDT, undefined) {
  "use strict";

  var configValueEditor;

  configValueEditor = function (values) {
    var edtr, elm, form;

    form = CDT.widget.form();
    //form.addReadOnly('Name', 'Name');
    form.addTextArea('Value', 'Value');

    form.setData(values);
    form.elm.data('original-values', values);

    elm = $('<div/>')
      .attr('title', 'Edit ' + values.Name)
      .append(form.elm);

    edtr = {};
    eventuality(edtr);

    edtr.show = function () {
      elm.dialog('open');
    };

    edtr.close = function () {
      elm.dialog('close');
      elm.dialog('destroy');
      elm.remove();
    };

    elm.dialog({
      autoOpen: false,
      width: 605,
      model: true,
      buttons: {
        'Save': function () {
          edtr.fire({
            type: 'save',
            data: form.getData()
          });
        },
        'Reset': function () {
          form.setData(form.elm.data('original-values'));
        },
        'Cancel': function () {
          edtr.close();
        }
      }
    });

    return edtr;
  };

  if (CDT.cmp === undefined) {
    CDT.cmp = {};
  }

  CDT.cmp.configurationEditor = function () {
    var elm, btn, list, srvc, loadList;

    srvc = window['conductor_model_ConfigValueCrud'];

    list = CDT.widget.list();
    list.addColumn('Name', 'Name');
    list.addColumn('Value', 'Value');
    loadList = function () {
      var spf = {
        filter: {
          editable: true
        }
      };
      srvc.retrieve(spf, function (data) {
        list.populate(data.data);
      });
    }

    btn = $('<button/>')
      .text('Edit')
      .button()
      .click(function () {
        var selected, configId, edtr;
        
        selected = list.getSelected().last().data('row-attributes');
        configId = selected['Id'];
        edtr = configValueEditor(selected);

        edtr.on('save', function (e) {
          var data = e.data;
          data.Id = configId;
          srvc.update(data, function () {
            edtr.close();
            loadList();
          });
        }).show();
      });

    elm = $('<div/>')
      .append(btn)
      .append(list.elm);

    list.on('selection-change', function () {
      var selected = list.getSelected();
      btn.button('option', 'disabled', selected.length === 0);
    });

    loadList();

    return elm;
  };

} (jQuery, CDT));
