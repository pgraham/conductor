(function ($, CDT, undefined) {
  "use strict";

  var createLbl, spinner, textArea, textInput;
  
  createLbl = function (forInput, lbl) {
    return $('<label/>')
      .attr('for', forInput)
      .text(lbl)
      .css('float', 'left');
  };

  spinner = function (name) {
    var elm = $('<span/>')
      .append( $('<input/>').attr('name', name).attr('value', '') );
    
    elm.find('input').spinner({ step: 1 });

    return {
      name: name,
      elm: elm,
      getValue: function () {
        return elm.find('input').spinner('value');
      },
      setValue: function (val) {
        elm.find('input').spinner('value', val);
      }
    };
  };

  textArea = function (name) {
    var elm = $('<textarea/>')
      .attr('name', name)
      .addClass('text')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all');

    return {
      name: name,
      elm: elm,
      getValue: function () {
        return elm.val() === ''
          ? null
          : elm.val();
      },
      setValue: function (val) {
        elm.val(val);
      }
    };
  };

  textInput = function (name) {
    var elm = $('<input type="text"/>')
      .attr('name', name)
      .addClass('text')
      .addClass('ui-widget-content')
      .addClass('ui-corner-all');
    
    return {
      name: name,
      elm: elm,
      getValue: function () {
        return elm.val() === ''
          ? null
          : elm.val();
      },
      setValue: function (val) {
        elm.val(val);
      }
    };
  };

  if (CDT.widget === undefined) {
    CDT.widget = {};
  }

  CDT.widget.form = function () {
    var elm, flds, inputs,
        labelWidth, colWidth,
        addInput;

    inputs = {};
    flds = $('<fieldset/>');
    elm = $('<form/>')
      .addClass('cdt-form')
      .append(flds);

    addInput = function (input, lbl) {
      inputs[input.name] = input;
      flds.append(createLbl(input.name, lbl));
      flds.append(input.elm);
    };

    return {
      elm: elm,

      addSpinner: function (name, lbl) {
        addInput(spinner(name), lbl);
      },
      addTextArea: function (name, lbl) {
        addInput(textArea(name), lbl);
      },
      addTextInput: function (name, lbl) {
        addInput(textInput(name), lbl);
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
    };
  };

} (jQuery, CDT));
