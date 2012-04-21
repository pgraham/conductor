(function ($, CDT, undefined) {
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
      .text(lbl)
      .css('float', 'left');
  }

  function fieldify(elm, overrides) {
    return $.extend(elm, defaultFieldOps, overrides || {});
  }

  CDT.form = {};

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
    elm = $('<form/>').addClass('cdt-form');
    flds = $('<fieldset/>').appendTo(elm);

    addInput = function (input, lbl) {
      inputs[input.getName()] = input;
      flds.append(createLbl(input.getName(), lbl));
      flds.append(input);
    };

    return $.extend(elm, {

      addSpinner: function (name, lbl) {
        addInput(CDT.form.spinner(name), lbl);
      },
      addTextArea: function (name, lbl) {
        addInput(CDT.form.textArea(name), lbl);
      },
      addTextInput: function (name, lbl) {
        addInput(CDT.form.textInput(name), lbl);
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
      }
    });
  };

} (jQuery, CDT));
