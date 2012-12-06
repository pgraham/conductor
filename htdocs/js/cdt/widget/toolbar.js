"use strict";
(function ($, CDT, exports, undefined) {

  $.widget('ui.toolbar', {
    options: {},

    _create: function () {
      this.element.addClass('cdt-toolbar');
    },

    _setOption: function (key, value) {
    },

    _destroy: function () {
    },

    addButton: function (lbl, btnOpts, hdlr) {
      this.element.append($('<button/>').text(lbl).button(btnOpts).click(hdlr));
    },

    addSeparator: function () {
      this.element.append($('<span/>').addClass('separator'));
    }
  });

} (jQuery, CDT, CDT.ns('CDT.widget')));
