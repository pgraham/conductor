(function (global, undefined) {
  "use strict";

  var dict = ${json:strings};

  if (global._L === undefined) {
    global._L = function () {
      var str, key, args = Array.prototype.slice.call(arguments);

      key = args.shift();

      if (dict[key] === undefined) {
        return 'XXXXXXXX ' + key + ' XXXXXXXX';
      }

      str = dict[key].md;
      str = String.prototype.format.apply(str, args);
      return str;
    }
  }
} (window));
