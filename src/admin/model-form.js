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
        props.${idProperty} = model.id;

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

${each:inputs as input}
  ${input}
${done}
