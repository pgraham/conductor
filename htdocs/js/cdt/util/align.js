"use strict";
(function ($, exports, undefined) {

  exports.align = function (elm, align) {
    switch (align) {
      case 'center':
      elm.css('text-align', 'center');
      break;

      case 'right':
      elm.css('text-align', 'right');
      break;

      case 'left':
      elm.css('text-align', 'left');
      break;
    }
  };

} (jQuery, CDT.ns('CDT.util')));
