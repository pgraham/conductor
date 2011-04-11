var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<input />')
    .attr('type', 'checkbox')
    .attr('name', '${property}')
    .attr('value', '${property}')
    .addClass('check');

  that = {
    elm: elm,
    name: '${property}',
    lbl: '${label}'
  };

  hasValue(that, {
    initial: value,
    getValue: function () {
      return elm.is(':checked');
    },
    setValue: function (val) {
      if (val) {
        elm.val([ '${property}' ]);
      } else {
        elm.val([]);
      }
    }
  });

  return that;
}
