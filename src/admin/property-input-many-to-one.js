/**
 * Input widget for a many-to-one relationship.  This amount to a remotely
 * populated select box.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
var ${model}_${relationship}_input = function (model) {
  var that, input, selected;

  input = $('<select />').attr('name', '${relationship}');

  window['${rhsCrudService}'].retrieve({}, function (data) {
    var i, len, opt;
    for (i = 0, len = data.length; i < len; i++) {
      opt = $('<option />')
        .attr('value', data[i]['${rhsIdProperty}'])
        .addClass('ui-widget-content')
        .addClass('ui-corner-all')
        .text(data[i]['${nameProperty}']);

      input.append(opt);
    }
  });

  that = {
    elm: input,
    name: '${relationship}',
    lbl: '${label}'
  };

  hasValue(that, {
    initial: model !== null ? model['${relationship}'] : null,
    getValue: function () {
      return input.val();
    },
    setValue: function (val) {
      input.val(val);
    }
  });

  return that;
};
