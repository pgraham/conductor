function ${model}_${property}Input() {
  this.label = $('<label/>')
    .attr('for', '${property}')
    .text('${label}');

  this.input = $('<input />')
    .attr('type', 'checkbox')
    .attr('name', '${property}')
    .attr('value', '${property}')
    .addClass('check');
}

${model}_${property}Input.prototype = {

  getLabel: function () {
    return this.label;
  },

  getInput: function () {
    return this.input;
  },

  getValue: function () {
    var ret = this.input.is(':checked');
    return ret;
  },

  setValue: function (val) {
    if (val) {
      this.input.val([ '${property}' ]);
    } else {
      this.input.val([]);
    }
  }
};
