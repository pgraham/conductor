(function (global, $, undefined) {
  "use strict";

  var dict = ${json:strings};

  function noValue(key) {
    return 'XXXXXXXX ' + key + ' XXXXXXXX';
  }

  function formatValue(val, args) {
    return String.prototype.format.apply(val, args);
  }

  function extractValue(type, val) {
    if ($.isArray(val)) {
      return val[type];
    }
    return val;
  }

  if (global._L === undefined) {
    global._L = function () {
      var key, args = Array.prototype.slice.call(arguments);

      key = args.shift();

      if (dict[key] === undefined) {
        return noValue(key);
      } else {
        return formatValue(extractValue('raw', dict[key]), args);
      }
    };
  }

  if (global._MD === undefined) {
    global._MD = function () {
      var key, args = Array.prototype.slice.call(arguments);

      key = args.shift();

      if (dict[key] === undefined) {
        return noValue(key);
      } else {
        return formatValue(extractValue('md', dict[key]), args);
      }
    };
  }

} (window, jQuery));
