var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<textarea/>')
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
      // Normalize empty string to null
      if (elm.val() === '') {
        return null;
      } else {
        return elm.val();
      }
    },
    setValue: function(val) {
      elm.val(val);
    }
  });

  return that;
};
