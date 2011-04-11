/**
 * Conductor admin interface.
 */
(function ($, undefined) {
  var models = ${json:models},
      menu, ctnt, editor,
      i, len;

  editor = function (spec) {
    var that, elm, grid, head, headers, rows, cols,
      appendTo, getSelected, refresh;

    elm = $('<div/>')
      .addClass('cdt-ModelEditor')
      .addClass('ui-widget')
      .append(
        $('<div/>')
          .addClass('cdt-ModelEditorBtns'))
      .append(
        $('<table/>')
          .addClass('cdt-ModelEditorGrid'));

    grid = elm.find('table')
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
          .addClass('cdt-ModelEditorRow')
          .append($('<th>&nbsp;</th>')));

    headers = head.find('tr');
    for (i = 0, len = spec.cols.length; i < len; i++) {
      headers.append( $('<th>' + spec.cols[i].lbl + '</th>') );
    }

    rows = grid.find('tbody');
    cols = [ {
      field: 'selected',
      html: true
    } ];
    for (i = 0, len = spec.cols.length; i < len; i++) {
      cols.push(spec.cols[i].id);
    }

    elm.find('.cdt-ModelEditorBtns')
      .append(
        $('<button/>')
          .attr('type', 'button')
          .text('New')
          .click(function () {
            spec.form(null).show(function () {
              that.refresh();
            });
          }))
      .append(
        $('<button/>')
          .attr('type', 'button')
          .text('Edit')
          .click(function () {
            var sel = that.getSelected(),
                id  = sel.length > 0
                  ? parseInt(sel.last().val())
                  : null,
                model = id !== null
                  ? $.ui.datastore.main.get(spec.type, id)
                  : null;

            if (model !== null) {
              spec.form(model.options.data).show(function () {
                that.refresh();
              });
            }
          }))
      .append(
        $('<button />')
          .attr('type', 'button')
          .text('Delete')
          .click(function () {
            var sel    = that.getSelected(),
                models = [];

            if (sel.length === 0) {
              return;
            }

            sel.each(function (idx, elm) {
              var id    = parseInt($(this).val()),
                  model = $.ui.datastore.main.get(spec.type, id);

              models.push(model.options.data);
            })

            spec.del(models).show(function () {
              that.refresh();
            });
          }));

    grid.grid({
      type    : spec.type,
      columns : cols
    });

    that = {
      elm  : elm,
      grid : grid,
      head : head,
      rows : rows
    };

    appendTo = function (jq) {
      elm.appendTo(jq);
      return this;
    };
    that.appendTo = appendTo;

    getSelected = function () {
      var sel = grid.find('input:checkbox:checked');
      return sel;
    };
    that.getSelected = getSelected;

    refresh = function () {
      $.ui.datasource.types[spec.type].get( $.ui.datastore.main );
    };
    that.refresh = refresh;

    return that;
  };

  ${each:editors as editor}
    ${editor}
  ${done}

  form = function (spec) {
    var that, model, elm, fieldset, btns, show, submit, reset, close, i, len;

    model = (spec.model && typeof spec.model === 'object') ? spec.model : null;

    elm = $('<div/>')
      .attr('title', model !== null ? 'Edit ' + spec.name : 'New ' + spec.name)
      .append($('<form><fieldset/></form>'));

    fieldset = elm.find('fieldset');
    for (i = 0, len = spec.inputs.length; i < len; i++) {
      fieldset.append(
        $('<label/>')
          .attr('for', spec.inputs[i].name)
          .text(spec.inputs[i].lbl));

      fieldset.append(spec.inputs[i].elm);
    }

    reset = function () {
      var i, len;
      for (i = 0, len = spec.inputs.length; i < len; i++) {
        spec.inputs[i].reset();
      }
    };

    submit = function (submitCb) {
      var props = {}, i, len;
      for (i = 0, len = spec.inputs.length; i < len; i++) {
        props[spec.inputs[i].name] = spec.inputs[i].getValue();
      }

      if (model === null) {
        spec.create(props, submitCb);
      } else {
        spec.update(model.guid, props, submitCb);
      }
    };

    that = {};

    show = function (submitCb) {
      var onSubmit = function () {
        elm.dialog('close');
        submitCb();
      };
      if (model === null) {
        btns = {
          "Create" : function () {
            submit(onSubmit);
          }
        };
      } else {
        btns = {
          "Save" : function () {
            submit(onSubmit);
          },
          "Reset" : function () {
            reset();
          }
        }
      }

      elm.dialog({
        modal: true,
        buttons: btns,
        dialogClass: 'cdt-FormDialog',
        width: 505
      });
    };
    that.show = show;

    return that;
  };

  ${each:forms as form}
    ${form}
  ${done}

  $(document).ready(function () {
    menu = $('#menu ul');
    ctnt = $('#ctnt');

    ${each:modelNames as model}
      menu.append(
        $('<li/>').append(
          $('<a href="#">' + models['${model}'].name.plural + '</a>')
            .click(function () {
              ${model}_editor().appendTo($('#ctnt').empty());
            })));
    ${done}

    menu.menu();
    menu.find('li a').first().click();
  });
} (jQuery));
