var ${model}_form = function (model) {
  var that, inputs = [], genInputs = [], tabs = [], btns, title, fieldset, i,
    len;

  ${each:properties as property}
    genInputs.push(${model}_${property}_input(model !== null
      ? model.${property}
      : null));
    inputs.push(genInputs[genInputs.length - 1]);
  ${done}

  if (genInputs.length > 0) {
    tabs["General"] = $('<form><fieldset/></form>');
    fieldset = tabs["General"].find('fieldset');
    for (i = 0, len = genInputs.length; i < len; i += 1) {
      fieldset.append(
        $('<label/>')
          .attr('for', genInputs[i].name)
          .text(genInputs[i].lbl));

      fieldset.append(genInputs[i].elm);
    }
  }

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
