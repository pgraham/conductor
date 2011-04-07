function ${model}Form(editor, model) {
  this.editor = editor;
  this.model = model;
  this.dialog = $('<div/>')
    .attr('title', (model === null)
      ? 'New ' + models['${model}'].name.singular
      : 'Edit ' + models['${model}'].name.singular);

  if (model === null) {
    this.btns = {
      "Create": (function (form) {
        return function () {
          form.submit();
        };
      } (this)),
      "Cancel": (function (form) {
        return function () {
          form.cancel();
        };
      } (this))
    };
  } else {
    this.btns = {
      "Save": (function (form) {
        return function () {
          form.submit();
        }
      } (this)),
      "Reset": (function (form) {
        return function () {
          form.reset();
        };
      } (this))
    };
  }

  this.form = $('<form/>');
  this.fieldSet = $('<fieldset/>');
  this.inputs = {};
  ${each:properties as property}
    this.inputs.${property} = new ${model}_${property}Input();
  ${done};
  numProps = this.inputs.length;
  for (prop in this.inputs) {
    this.fieldSet.append(this.inputs[prop].getLabel());
    this.fieldSet.append(this.inputs[prop].getInput());
  }
  this.form.append(this.fieldSet);
  this.dialog.append(this.form);

  this.reset();

  $('body').append(this.dialog);
  this.dialog.dialog({
    modal: true,
    buttons: this.btns,
    dialogClass: 'cdt-FormDialog',
    width: 505,
    close: (function (form) {
      return function () {
        form.cancel();
      };
    } (this))

  });
}

${model}Form.prototype = {

  cancel: function () {
    this.dialog.dialog('destroy');
    this.dialog.detach();
  },

  reset: function () {
    ${each:properties as property}
      this.inputs.${property}.setValue(this.model !== null
        ? this.model.${property}
        : null);
    ${done}
  },

  submit: function () {
    var props = {},
        onDone = (function (form) {
          return function (data) {
            form.cancel();
            form.editor.refresh();
          };
        } (this));
    ${each:properties as property}
      props.${property} = this.inputs.${property}.getValue();
    ${done}

    if (this.model === null) {
      ${crudServiceVar}.create(props, onDone);
    } else {
      props.id = this.model.guid;
      ${crudServiceVar}.update(props, onDone);
    }
  }
};

${each:propertyInputs as input}
  ${input}
${done}
