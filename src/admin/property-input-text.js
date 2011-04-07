function ${model}_${property}Input() {
  this.label = $('<label/>')
    .attr('for', '${property}')
    .text('${label}');

  this.input = $('<textarea/>')
    .attr('name', '${property}')
    .addClass('text')
    .addClass('ui-widget-content')
    .addClass('ui-corner-all');
}

${model}_${property}Input.prototype = {

  getLabel: function () {
    return this.label;
  },

  getInput: function () {
    return this.input;
  },

  getValue: function () {
    return this.input.val();
  },

  setValue: function (val) {
    this.input.val(val);
  }
};
