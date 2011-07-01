/**
 * Input widget for timestamps.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
var ${model}_${property}_input = function (value) {
  var that, elm;

  elm = $('<input/>')
    .attr('type', 'text')
    .attr('name', '${property}')
    .addClass('text')
    .addClass('ui-widget-content')
    .addClass('ui-corner-all')
    .datetimepicker({
      ampm: true,
      dateFormat: 'MM d, yy',
      timeFormat: 'h:mm tt',
      separator: ' @ '
    });

  that = {
    elm: elm,
    name: '${property}',
    lbl:  '${label}'
  };

  hasValue(that, {
    initial: value,
    getValue: function () {
      var ts = elm.datetimepicker('getDate');
      return ts.toUTCString();
    },
    setValue: function (val) {
      var date;
      if (val === null) {
        date = null
      } else {
        date = Date.utcToLocal(val);
      }

      elm.datetimepicker('setDate', date);
    }
  });

  return that;
};
