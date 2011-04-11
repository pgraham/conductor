var ${model}_form = function (model) {
  var that, inputs = [];

  ${each:properties as property}
    inputs.push(${model}_${property}_input(model !== null
      ? model.${property}
      : null));
  ${done}

  that = form({
    type: '${model}',
    name: '${singular}',
    model: model,
    inputs: inputs,
    create: function (props, cb) {
      window['${crudServiceVar}'].create(props, cb);
    },
    update: function (id, props, cb) {
      props.${idProperty} = id;
      window['${crudServiceVar}'].update(props, cb);
    }
  });

  return that;
};

${each:propertyInputs as input}
  ${input}
${done}
