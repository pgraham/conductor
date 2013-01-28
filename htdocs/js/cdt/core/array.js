(function (exports, undefined) {
  "use strict";

  if (!exports.map) {
    exports.map = function (cb, thisArg) {
      var T, A, k;
 
      if (this == null) {
        throw new TypeError(" this is null or not defined");
      }
   
      if (typeof callback !== "function") {
        throw new TypeError(callback + " is not a function");
      }
   
      var O = Object(this);
      var len = O.length >>> 0;
   
      if (thisArg) {
        T = thisArg;
      }
   
      A = new Array(len);
      k = 0;
      while(k < len) {
        var kValue, mappedValue;

        if (k in O) {
          kValue = O[ k ];
          mappedValue = callback.call(T, kValue, k, O);
          A[ k ] = mappedValue;
        }
        k++;
      }
   
      return A;
    };
  }

} (Array.prototype));
