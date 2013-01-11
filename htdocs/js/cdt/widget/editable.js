"use strict";
(function (exports, $, CDT, undefined) {

  $.widget('ui.editable', {
    options: {},

    _create: function () {
      var self = this;

      this.editorOpen = false;
      this.editor = $('<input/>')
        .addClass('inplace-editor')
        .hide()
        .appendTo('body')
        .css('position', 'absolute')
        .attr('title', _L('tt.edit.toSave'))
        .tooltip()
        .blur(function () {
          self._hideEditor();
        })
        .keyup(function (e) {
          if (e.which === KeyEvent.DOM_VK_RETURN) {
            self._submit();
          } else if (e.which === KeyEvent.DOM_VK_ESCAPE) {
            self._hideEditor();
          }
        });

      this.textEl = $('<span/>').text(this.element.text());

      this.editBtn = $('<button/>')
        .button({
          label: _L('lbl.edit').ucfirst(),
          text: false,
          icons: { primary: 'ui-icon-pencil' }
        })
        .click(function () {
          self._showEditor();
        });

      if (this.options.editButtonTooltip) {
        this.editBtn.attr('title', this.options.editButtonTooltip).tooltip();
      }

      this.element
        .empty()
        .append(this.textEl)
        .append(this.editBtn)
        .addClass('editable');
    },
    _setOptions: function (key, value) {
    },
    _destroy: function () {
      this.editor.remove();
      this.editBtn.button('destroy').remove();

      this.element.text(this.textEl.text());
      this.textEl.remove();
    },

    _hideEditor: function () {
      var self = this;

      if (!this.editorOpen) {
        return;
      }
      this.editorOpen = false;

      // Queue effects on an empty jQuery object so that they happen
      // sequentially
      $({})
        .queue(function (next) {
          self.editor.blur();
          self.editor.fadeOut('fast', next);
        })
        .queue(function (next) {
          self.textEl.fadeTo('fast', 1);
          self.editBtn.fadeIn('fast', next);
        });
    },

    _showEditor: function () {
      var self = this;

      if (this.editorOpen) {
        return;
      }
      this.editorOpen = true;

      // Briefly show the editor to size/position it then hide it again so
      // that its display can be animated
      this.editor
        .val(this.textEl.text())
        .show()
        .width(this.element.width())
        .position({
          my: 'left top',
          at: 'left top',
          of: this.textEl
        })
        .hide();

      $({})
        .queue(function (next) {
          self.textEl.fadeTo('fast', 0.05);
          self.editBtn.fadeOut('fast', next);
        })
        .queue(function (next) {
          self.editor.fadeIn('fast', next);
        })
        .queue(function (next) {
          self.editor.focus();
          next();
        });
    },

    _submit: function () {
      var self = this, newVal = this.editor.val();

      this.options.save(newVal, function () {
        self.textEl.text(newVal);
        self._hideEditor();
      });
    }
  });

  exports.editable = function (opts) {
    return $('<div/>').editable(opts || {});
  };

} (CDT.ns('CDT.widget'), jQuery, CDT));
