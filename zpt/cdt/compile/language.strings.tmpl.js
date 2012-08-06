(function (global, undefined) {
  "use strict";

  var dict = ${json:strings};

  if (global._L === undefined) {
    global._L = function (key) {

      if (dict[key] === undefined) {
        return 'XXXXXXXX ' + key + ' XXXXXXXX';
      }
      return dict[key].md;
    }
  }
} (window));
