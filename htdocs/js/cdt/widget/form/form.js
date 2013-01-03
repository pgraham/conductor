"use strict";
(function (exports, $, CDT, undefined) {

  function createLbl(forInput, lbl) {
    return $('<label/>')
      .attr('for', forInput)
      .text(lbl);
  }

  $.widget('ui.form', {
    options: {},

    _create: function () {
      this.element.addClass('cdt-form');
      this.fields = $('<fieldset/>').appendTo(this.element);
      this.inputs = {};

      this.element.layout('fillWith', 'fieldset');
    },
    _setOption: function (key, value) {
    },
    _destroy: function () {
    },

    addSubmit: function (btnOpts) {
      var input = $('<button/>')
        .appendTo(this.element)
        .button(btnOpts);

      if (btnOpts.handler) {
        input.click(btnOpts.handler);
      }
    },

    addListInput: function (name, lbl, opts) {
      var listInput = CDT.widget.formfield.listInput(name, opts);
      return this._addInput(name, lbl, listInput, opts);
    },

    addPassword: function (name, lbl, opts) {
      var password = CDT.widget.formfield.password(name, opts);
      return this._addInput(name, lbl, password, opts);
    },

    addSpinner: function (name, lbl, opts) {
      var spinner = CDT.widget.formfield.spinner(name, opts);
      return this._addInput(name, lbl, spinner, opts);
    },

    addTextInput: function (name, lbl, opts) {
      var textInput = CDT.widget.formfield.text(name, opts);
      return this._addInput(name, lbl, textInput, opts);
    },

    addTextArea: function (name, lbl, opts) {
      var textArea = CDT.widget.formfield.textarea(name, opts);
      return this._addInput(name, lbl, textArea, opts);
    },

    dialog: function (title, buttons) {
      var instance = this;

      buttons = buttons || {};
      if (!buttons['Cancel']) {
        buttons['Cancel'] = function () {
          instance.dialog.dialog('close');
        }
      }

      this.dialog = $('<div/>')
        .attr('title', title)
        .append(this.element)
        .dialog({
          buttons: buttons,
          hide: 600,
          modal: true,
          resizable: false,
          show: 200,
          width:800,
          close: function (ev, ui) {
            $(this).dialog('destroy');
          }
        });

      return this;
    },

    getData: function () {
      var data = {};
      $.each(this.inputs, function (name, input) {
        data[name] = input.formfield('getValue');
      });
      return data;
    },

    getInput: function (inputName) {
      return this.inputs[inputName];
    },

    setData: function (data) {
      $.each(this.inputs, function (name, input) {
        input.formfield('setValue', data[name]);
      });
      return this;
    },

    _addInput: function (name, lbl, input, opts) {
      var self = this;

      input.on('formfieldchange', function (e) {
        var data = self.getData();
        self._trigger('change', e, data);
      });

      this.inputs[name] = input;
      this.fields.append(createLbl(name, lbl));
      this.fields.append(input);

      if (opts.autoHeight === true) {
        this.fields.layout('fillWith', input);
      }

      return this;
    }
  });

  exports.formBuilder = function (opts) {
    opts = opts || {};
    var elm = $('<form />').form(opts);

    return {
      addInput: function (name, lbl, opts) {
        elm.form('addTextInput', name, lbl, opts || {});
        return this;
      },
      addListInput: function (name, lbl, opts) {
        elm.form('addListInput', name, lbl, opts || {});
        return this;
      },
      addPassword: function (name, lbl, opts) {
        elm.form('addPassword', name, lbl, opts || {});
        return this;
      },
      addTextArea: function (name, lbl, opts) {
        elm.form('addTextArea', name, lbl, opts || {});
        return this;
      },
      build: function () {
        return elm;
      },
    }
  };

} (CDT.ns('CDT.widget'), jQuery, CDT));
