var ${model}_form = function (model) {
  var that, inputs = [], genInputs = [], tabs = [], btns, title, fieldset, i,
    len, input;

  ${if:numProperties > 0}
    tabs["General"] = $('<form><fieldset/></form>');
    fieldset = tabs["General"].find('fieldset');
  ${fi}

  ${each:properties as property}
    input = ${model}_${property[id]}_input(model !== null
      ? model.${property[id]}
      : ${property[default]});
    genInputs.push(input);
    inputs.push(input);

    fieldset.append(
      $('<label/>')
        .attr('for', input.name)
        .text(input.lbl));

    fieldset.append(input.elm);
  ${done}

  ${each:tabs as tab}
    ${tab}
  ${done}

  if (model === null) {
    btns = {
      "Create" : function () {
        var props = {}, i, len;

        for (i = 0, len = inputs.length; i < len; i += 1) {
          props[inputs[i].name] = inputs[i].getValue();
        }
        
        window['${crudServiceVar}'].create(props, that.close);
      }
    };
    title = 'New ${singular}';
  } else {
    btns = {
      "Save" : function () {
        var props = {}, i, len;
        props.${idProperty} = model.${idProperty};

        for (i = 0, len = inputs.length; i < len; i += 1) {
          props[inputs[i].name] = inputs[i].getValue();
        }
        
        window['${crudServiceVar}'].update(props, that.close);
      },
      "Reset" : function () {
        var i, len;
        for (i = 0, len = inputs.length; i < len; i += 1) {
          inputs[i].reset();
        }
      }
    }
    title = 'Edit ${singular}';
  }

  that = CDT.tabbedDialog({
    tabs: tabs,
    btns: btns,
    title: title
  });

  return that;
};

${each:propInputs as input}
  var ${input[name]} = function (value) {
    var that, elm;

    ${if:input[type] = boolean}
      elm = $('<input />')
        .attr('type', 'checkbox')
        .attr('name', '${input[property]}')
        .attr('value', '${input[property]}')
        .addClass('check');

    ${elseif:input[type] = decimal or input[type] = integer}
      elm = $('<span />')
        .append($('<input />')
          .attr('name', '${input[property]}')
          .attr('value', '')
        );

      elm.find('input').spinner({
        ${if:input[type] = decimal}
          step: 0.01
        ${else}
          step: 1
        ${fi}
      });

    ${elseif:input[type] = string}
      elm = $('<input />')
        .attr('type', 'text')
        .attr('name', '${input[property]}')
        .addClass('text')
        .addClass('ui-widget-content')
        .addClass('ui-corner-all');

    ${elseif:input[type] = text}
      elm = $('<textarea />')
        .attr('name', '${input[property]}')
        .addClass('text')
        .addClass('ui-widget-content')
        .addClass('ui-corner-all');

    ${elseif:input[type] = timestamp}
      elm = $('<input />')
        .attr('type', 'text')
        .attr('name', '${input[property]}')
        .addClass('text')
        .addClass('ui-widget-content')
        .addClass('ui-corner-all')
        .datetimepicker({
          ampm: true,
          dateFormat: 'MM d, yy',
          timeFormat: 'h:mm tt',
          separator: ' @ '
        });

    ${fi}

    that = {
      elm: elm,
      name: '${input[property]}',
      lbl: '${input[label]}'
    };

    hasValue(that, {
      initial: value,
      getValue: function () {
        ${if:input[type] = boolean}
          return elm.is(':checked');

        ${elseif:input[type] = decimal or input[type] = integer}
          return elm.find('input').spinner('value');

        ${elseif:input[type] = string or input[type] = text}
          if (elm.val() === '') {
            return null;
          } else {
            return elm.val();
          }

        ${elseif:input[type] = timestamp}
          var ts = elm.datetimepicker('getDate');
          return ts.toUTCString();

        ${fi}
      },
      setValue: function (val) {
        ${if:input[type] = boolean}
          if (val) {
            elm.val([ '${input[property]}' ]);
          } else {
            elm.val([]);
          }

        ${elseif:input[type] = decimal or input[type] = integer}
          elm.find('input').spinner('value', val);

        ${elseif:input[type] = string or input[type] = text}
          elm.val(val);

        ${elseif:input[type] = timestamp}
          var date = (val !== null)
            ? Date.utcToLocal(val)
            : null;
          elm.datetimepicker('setDate', date);

        ${fi}
      }
    });

    return that;
  };
${done}

${each:relInputs as input}
  var ${input[name]} = function (entity) {
    ${if:input[type] = many-to-one}
      var that, elm, selected, getValue, setValue;

      elm = $('<select />').attr('name', '${input[relationship]}');

      window['${input[rhsCrudService]}'].retrieve({}, function (data) {
        var i, len, opt;
        for (i = 0, len = data.length; i < len; i++) {
          opt = $('<option />')
            .attr('value', data[i]['${input[rhsIdProperty]}'])
            .addClass('ui-widget-content')
            .addClass('ui-corner-all')
            .text(data[i]['${input[nameProperty]}']);

          elm.append(opt);
        }
      });

      getValue = function () {
        return elm.val();
      };

      setValue = function (val) {
        elm.val(val);
      };

    ${elseif:input[type] = one-to-many}
      var that, elm, grid, selected, getValue, setValue;

      if (entity !== null) {
        selected = function (request, response) {
          var spf = $.extend({
            filter: {
              ${input[rhsColumn]}: entity['${input[lhsIdProperty]}']
            }
          }, request);

          window['${input[rhsCrudService]}'].retrieve(spf,
            CDT.gridIdCallback(response, '${input[rhsIdProperty]}')
          );
        };
      } else {
        selected = [];
      }

      grid = CDT.modelDualGrid({
        nameProperty : '${input[nameProperty]}',
        dataitem     : typeof ${input[rhs]} === 'function'
                         ? ${input[rhs]}()
                         : {},
        selected     : selected,
        available    : function (request, response) {
          var spf = $.extend({
            filter: {
              ${input[rhsColumn]} : null
            }
          }, request);
          window['${input[rhsCrudService]}'].retrieve(
            spf, CDT.gridIdCallback(response, '${input[rhsIdProperty]}')
          );
        }
      });
      elm = grid.elm;

      getValue = function () {
        var selType = grid.selected.type,
            selItems = $.ui.datastore.main.get(selType).options.items,
            value = [], i, len;

        for (i = 0, len = selItems.length; i < len; i += 1) {
          value.push(selitems[i].options.data.guid);
        }

        return value;
      };

      setValue = function (val) {
        // Setting the value only happend on a reset operation so until this
        // changes we can simply reset the grids
        grid.available.refresh();
        grid.selected.refresh();
      };

    ${fi}

    that = {
      elm: elm,
      name: '${input[relationship]}'
      lbl: '${input[label]}'
    };

    hasValue(that, {
      initial: entity !== null
        ? entity['${input[relationship]}']
        : null,
      getValue: getValue,
      setValue: setValue
    });

    return that;
  };
${done}
