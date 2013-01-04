"use strict";
(function ($, CDT, exports, undefined) {

  $.widget('ui.toolbar', {
    options: {},

    _create: function () {
      this.element.addClass('cdt-toolbar');

      this._buttons = {};
      if (this.options.buttons) {
        this.addButtons(this.options.buttons);
      }
    },

    _setOption: function (key, value) {
    },

    _destroy: function () {
    },

    addButton: function (btnCfg) {
      var btn;
      if (typeof btnCfg === 'string') {
        switch (btnCfg) {
          case '-':
          this.addSeparator();
          break;

          default:
          this.addButton({ label: btnCfg });
          break;
        }
      } else {
        btn = $('<button/>')
          .button(btnCfg)
          .click(btnCfg.handler)
          .attr('title', btnCfg.tooltip ? btnCfg.tooltip : '')
          .tooltip();

        this.element.append(btn);
        this._buttons[btnCfg.label] = btn;
      }
      return this;
    },

    addButtons: function (btnCfgs) {
      var self = this;
      $.each(btnCfgs, function (idx, btnCfg) {
        self.addButton(btnCfg);
      });
      return this;
    },

    addSeparator: function () {
      this.element.append($('<span/>').addClass('separator'));
      return this;
    },

    clear: function () {
      var self = this;
      $.each(this._buttons, function (idx) {
        self.removeButton(idx);
      });
      this._buttons = {};
      return this;
    },

    disable: function (btnId) {
      this.setEnabled(btnId, false);
    },

    enable: function (btnId) {
      this.setEnabled(btnId, true);
    },

    getButton: function (btnId) {
      return this._buttons[btnId];
    },

    removeButton: function (btnId) {
      this._buttons[btnId].button('destroy').remove();
      delete this._buttons[btnId];
    },

    setEnabled: function (btnId, enabled) {
      this._buttons[btnId].button('option', 'disabled', !enabled);
    }
  });

  exports.toolbar = function (opts) {
    return $('<div/>').toolbar(opts);
  };

} (jQuery, CDT, CDT.ns('CDT.widget')));
