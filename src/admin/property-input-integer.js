var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<input />')
    .attr('name', '${property}')
    .attr('value', '0')
    .spinner({
      step: 1
    });

  that = {
    elm: elm,
    name: '${property}',
    lbl: '${label}'
  };

  hasValue(that, {
    initial: value,
    getValue: function () {
      return elm.spinner('value');
    },
    setValue: function (val) {
      elm.spinner('value', val);
    }
  });

  return that;
};
