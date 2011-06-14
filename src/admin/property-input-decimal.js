var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<input />')
    .attr('name', '${property}')
    .attr('value', '10.00')
    .spinner({
      min: 0,
      max: 9999.99, // TODO Get this from the property's annotations
      step: 0.01,
      start: 10.00,
      numberformat: "n"
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
}
