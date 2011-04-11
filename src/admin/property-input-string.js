var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<input/>')
    .attr('type', 'text')
    .attr('name', '${property}')
    .addClass('text')
    .addClass('ui-widget-content')
    .addClass('ui-corner-all');

  that = {
    elm: elm,
    name: '${property}',
    lbl: '${label}'
  };

  hasValue(that, {
    initial: value,
    getValue: function () {
      return elm.val();
    },
    setValue: function (val) {
      elm.val(val);
    }
  });

  return that;
}
