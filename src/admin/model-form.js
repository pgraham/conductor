function ${model}Form(model) {
  var props;

  this.model = model;
  this.dialog = $('<div/>')
    .attr('title', (model === null)
      ? 'New ' + models['${model}'].name.singular
      : 'Edit ' + models['${model}'].name.singular);

  this.btns = $('<div/>');
  if (model === null) {
    this.btns
      .append(
        $('<input type="button" value="Create" />').click(function () {
          this.submit();
        }))
      .append(
        $('<input type="button" value="Cancel" />').click(function () {
          this.cancel();
        }));
  } else {
    this.btns
      .append(
        $('<input type="button" value="Save" />').click(function () {
          this.submit();
        }))
      .append(
        $('<input type="button" value="Reset" />').click(function () {
        }))
      .append(
        $('<input type="button" value="Cancel" />').click(function () {
          this.dialog.dialog('destroy');
          this.dialog.detach();
        }));
  }

  this.form = $('<form/>');
  this.fieldSet = $('<fieldset/>');
  this.inputs = {};
  ${each:properties as property}
    this.inputs.${property} = (new ${model}_${property}Input());
  ${done};
  numProps = this.inputs.length;
  for (prop in this.inputs) {
    fieldSet.append(this.inputs[prop].getLabel());
    fieldSet.append(this.inputs[prop].getInput());
  }
  this.form.append(this.fieldSet);
  this.dialog.append(this.form);

  this.reset();

  $('body').append(this.dialog);
  this.dialog.dialog();
}

${model}Form.prototype = {

  cancel: function () {
    this.dialog.dialog('destroy');
    this.dialog.detach();
  },

  reset: function () {
  },

  submit: function () {
    ${each:properties as property}
      this.inputs['${property}'].setValue(this.model.${property}));
    ${done}
  }
};

${each:propertyInputs as input}
  ${input}
${done}
