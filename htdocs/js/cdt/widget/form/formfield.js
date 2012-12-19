"use strict";
(function (exports, $, CDT, undefined) {

  /*
   * jQuery ui widget factory to decorate a form input component with
   * additional functionality.
   */
  $.widget('ui.formfield', {
    options: {
      selectOnFocus: false
    },

    _create: function () {
      var self = this;

      this._invalidEl = this.options.invalidEl || this.element;
      this._inputEl = this.options.inputEl || this.element;

      this.element
        .addClass('cdt-formfield')
        .addClass('ui-widget-content')
        .addClass('ui-corner-all')
        .change(function () {
          self.clearInvalid();
        });

      if (this.options.placeholder) {
        this._inputEl.attr('placeholder', this.options.placeholder);
      }

      if (this.options.autoWidth) {
        this._inputEl.addClass('cdt-formfield-fill-width');
      }

      this._inputEl.bind('input', function (e) {
        self._trigger('change', e);
      });
    },
    _setOption: function (key, value) {
    },
    _destroy: function () {
    },

    clearInvalid: function () {
      this._invalidEl.removeClass('invalid');
    },

    getName: function () {
      return this._inputEl.attr('name');
    },

    getValue: function () {
      var val;
      
      if (this.options.getValue) {
        val = this.options.getValue.apply(this);
      } else {
        val = this._inputEl.val();
      }

      // Normalize empty string with null
      return val === '' ? null : val;
    },

    markInvalid: function () {
      this._invalidEl.addClass('invalid');
    },

    setValue: function (val) {
      if (this.options.setValue) {
        this.options.setValue.apply(this, [ val ]);
      } else {
        this._inputEl.val(val);
      }
    }
  });

  // ---------------------------------------------------------------------------
  // Sugar for creating form inputs
  // ---------------------------------------------------------------------------

  var defaultSpinnerOpts = { step: 1 },
      passwordOpts = { autoWidth: true },
      textOpts = { autoWidth: true },
      textareaOpts = { autoWidth: true };

  exports.formfield = {
    listInput: function (name, opts) {
      var cont, ol, addBtn, input;

      function addListItem(val) {
        var li = $('<li/>')
          .append(
            $('<input/>')
              .addClass('ui-widget-content ui-corner-all')
              .attr('type', 'text')
              .val(val || '')
              .on('input', function (e) {
                cont.formfield( 'clearInvalid' );
                cont.data('uiFormfield')._trigger('change', e);
              })
          )
          .append(
            $('<button/>')
              .button({
                label: 'Remove',
                text: false,
                icons: { primary: 'ui-icon-closethick' }
              })
              .click(function (e) {
                li.remove();
                cont.formfield( 'clearInvalid' );
                cont.data('uiFormfield')._trigger('change', e);
              })
          )
          .layout('h-fill', 'input[type="text"]').appendTo(ol);
      }

      cont = $('<div class="list-input" />');
      ol = $('<ol/>').appendTo(cont);
      addBtn = $('<button/>')
        .button({
          label: 'Add item',
          text: false,
          icons: { primary: 'ui-icon-plusthick' }
        })
        .click(function (e) {
          e.preventDefault();
          addListItem();
        }).appendTo(cont);

      input = $('<input type="hidden"/>').attr('name', name).appendTo(cont);

      return cont.formfield($.extend({
        invalidEl: ol,
        inputEl: input,
        getValue: function () {
          var val = [];
          cont.find('input[type="text"]').each(function () {
            val.push($(this).val());
          });
          return val;
        },
        setValue: function (val) {
          ol.empty();
          $.each(val, function (idx) {
            addListItem(this);
          });
        }
      }, opts));
    },

    password: function (name, opts) {
      return $('<input class="password" type="password"/>')
        .attr('name', name)
        .formfield($.extend({}, passwordOpts, opts));
    },

    spinner: function (name, opts) {
      var cont, elm, input;

      cont = $('<span/>');
      input = $('<input />')
        .attr('name', name)
        .attr('value', '')
        .appendTo(cont)
        // Order is important, input must be attached before spinner(...) is
        // called
        .spinner($.extend({}, defaultSpinnerOpts))
        .bind('spin', function () {
          cont.formfield('clearInvalid');
        });

      elm = cont.find('.ui-spinner');

      // the latest version of the spinner widget sets an explicit height on the
      // element the same as the calculated height to fix an IE6 bug, but since
      // the spinner isn't attached yet the calculated height is 0.  Since IE6
      // is not supported by conductor app the height: 0 is simply cleared
      elm.css('height', '');

      return cont.formfield($.extend({
        invalidEl: elm,
        inputEl: input
      }, opts));
    },

    text: function (name, opts) {
      return $('<input class="text" type="text"/>')
        .attr('name', name)
        .formfield($.extend({}, textOpts, opts));
    },

    textarea: function (name, opts) {
      return $('<textarea/>')
        .attr('name', name)
        .formfield($.extend({}, textareaOpts, opts));
    }
  };

} (CDT.ns('CDT.widget'), jQuery, CDT));
