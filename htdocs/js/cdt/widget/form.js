(function ($, CDT, exports, undefined) {
  "use strict";

  var defaultSpinnerOpts = { step: 1 },
      defaultFieldOps = {
        clearInvalid: function () {
          this.removeClass('invalid');
        },
        getName: function () {
          return this.attr('name');
        },
        getValue: function () {
          return this.val() === ''
            ? null
            : this.val();
        },
        markInvalid: function () {
          this.addClass('invalid');
        },
        setValue: function (val) {
          this.val(val);
        }
      };

  function createLbl(forInput, lbl) {
    return $('<label/>')
      .attr('for', forInput)
      .text(lbl);
  }

  function fieldify(elm, overrides) {
    return $.extend(elm, defaultFieldOps, overrides || {});
  }

  CDT.form = {};

  CDT.form.password = function (name, opts) {
    var elm = $('<input type="password"/>')
      .attr('name', name)
      .addClass('password')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all')
      .change(function () {
        elm.clearInvalid();
      });
    
    return fieldify(elm);
  };

  CDT.form.spinner = function (name, opts) {
    var cont, elm, inpt;
    
    cont = $('<span/>');
    inpt = $('<input type="text"/>')
      .attr('name', name)
      .attr('value', '')
      .appendTo(cont)
      // Order is important, input must be attached before spinner(...) is
      // called
      .spinner($.extend({}, defaultSpinnerOpts, opts || {}))
      .bind('spin', function () {
        cont.clearInvalid();
      });

    elm = cont.find('.ui-spinner');

    // the latest version of the spinner widget sets an explicit height on the
    // element the same as the calculated height to fix an IE6 bug, but since
    // the spinner isn't attached yet the calculated height is 0.  Since IE6 is
    // not supported by conductor app the height: 0 is simply cleared
    elm.css('height', '');

    return fieldify(cont, {
      clearInvalid: function () {
        elm.removeClass('invalid');
      },
      getName: function () {
        return name;
      },
      getValue: function () {
        return inpt.spinner('value');
      },
      markInvalid: function () {
        elm.addClass('invalid');
      },
      setValue: function (val) {
        inpt.spinner('value', val);
      }
    });
  };

  CDT.form.textArea = function (name) {
    var elm = $('<textarea/>')
      .attr('name', name)
      .addClass('text')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all')
      .change(function () {
        elm.clearInvalid();
      });

    return fieldify(elm);
  };

  CDT.form.textInput = function (name) {
    var elm = $('<input type="text"/>')
      .attr('name', name)
      .addClass('text')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all')
      .change(function () {
        elm.clearInvalid();
      });
    
    return fieldify(elm);
  };

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  CDT.widget.form = function () {
    var elm, flds, inputs,
        labelWidth, colWidth,
        addInput;

    inputs = {};
    elm = $('<form class="ui-widget cdt-form"/>');
    flds = $('<fieldset/>').appendTo(elm);

    addInput = function (input, lbl) {
      inputs[input.getName()] = input;
      flds.append(createLbl(input.getName(), lbl));
      flds.append(input);
    };

    return $.extend(elm, {
      addPassword: function (name, lbl) {
        addInput(CDT.form.password(name), lbl);
        return this;
      },
      addSpinner: function (name, lbl) {
        addInput(CDT.form.spinner(name), lbl);
        return this;
      },
      addTextArea: function (name, lbl) {
        addInput(CDT.form.textArea(name), lbl);
        return this;
      },
      addTextInput: function (name, lbl) {
        addInput(CDT.form.textInput(name), lbl);
        return this;
      },
      getData: function () {
        var data = {};
        $.each(inputs, function (name, input) {
          data[name] = input.getValue();
        });
        return data;
      },
      setData: function (data) {
        $.each(inputs, function (name, input) {
          input.setValue(data[name]);
        });
        return this;
      }
    });
  };

  $.widget('ui.formfield', {
    options: {},

    _create: function () {
    },
    _setOption: function (key, value) {
    },
    _destroy: function () {
    },

    clearInvalid: function () {
      this.element.removeClass('invalid');
    },
    getName: function () {
      return this.element.attr('name');
    },
    getValue: function () {
      return this.element.val() === ''
        ? null
        : this.element.val();
    },
    markInvalid: function () {
      this.element.addClass('invalid');
    },
    setValue: function (val) {
      this.element.val(val);
    }
  });

  $.widget('ui.form', {
    options: {},

    _create: function () {
      this.element.addClass('cdt-form');
      this.fields = $('<fieldset/>').appendTo(this.element);
      this.inputs = {};
    },
    _setOption: function (key, value) {
    },
    _destroy: function () {
    },

    addTextInput: function (name, lbl) {
      return this._addInput(CDT.form.textInput(name), lbl);
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

    setData: function (data) {
      $.each(this.inputs, function (name, input) {
        input.formfield('setValue', data[name]);
      });
      return this;
    },

    _addInput: function (input, lbl, overrides) {
      var name = input.formfield().formfield('getName');
      this.inputs[name] = input;
      this.fields.append(createLbl(name, lbl));
      this.fields.append(input);

      return this;
    }
  });

  exports.formBuilder = function () {
    var elm = $('<form />').form();

    return {
      addInput: function (name, lbl) {
        elm.form('addTextInput', name, lbl);
        return this;
      },
      build: function () {
        return elm;
      },
    }
  };

} (jQuery, CDT, CDT.ns('CDT.widget')));
