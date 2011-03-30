function ${model}_${property}Input() {
  this.label = $('<label/>')
    .attr('for', '${property}')
    .text('${label}');

  this.input = $('<input />')
    .attr('type', 'checkbox')
    .attr('name', '${property}')
    .addClass('check')
}

${model}_${property}Input.prototype = {

  getLabel: function () {
    return this.label;
  },

  getInput: function () {
    return this.input;
  },

  getValue: function () {
    return this.input.attr('checked');
  },

  setValue: function (val) {
    this.input.val(val);
  }
};
