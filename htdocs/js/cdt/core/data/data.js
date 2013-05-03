(function (exports, $, undefined) {
  "use strict";

  exports.buildAjaxErrorHandler = function (cb) {
    cb = cb || $.noop;

    return function (jqXhr, textStatus, errorThrown) {
      cb({
        success: false,
        msg: {
          text: jqXhr.responseText || jqXhr.responseXML || errorThrown,
          type: 'error'
        }
      });
    };
  };

} (CDT.ns('CDT.data'), jQuery));
