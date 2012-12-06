// Adapted from Javascript: The Good Parts by Douglas Crockford
/**
 * @deprecated Use observable instead.
 */
(function (exports, undefined) {
  "use strict";

  exports.eventuality = function (that) {
    console.log("DEPRECATED: cdt.core.eventuality() has been deprecated. " +
      "Use cdt.core.observable() instead");
    var registry = {};

    that.fire = function (event) {
      var array,
          func,
          handler,
          i,
          type = typeof event === 'string' ? event : event.type;

      if (registry.hasOwnProperty(type)) {
        array = registry[type];
        for (i = 0; i < array.length; i++) {
          handler = array[i];

          func = handler.method;
          if (typeof func === 'string') {
            func = this[func]
          }

          func.apply(this, handler.parameters || [ event ]);
        }
      }
      return this;
    };

    that.on = function (type, method, parameters) {
      var handler = {
        method: method,
        parameters: parameters
      };

      if (registry.hasOwnProperty(type)) {
        registry[type].push(handler);
      } else {
        registry[type] = [ handler ];
      }
      return this;
    };

    that.one = function (type, method, parameters) {
      that.on(type, function (e) {
        method.apply(this, [e]);
        that.off(type, method);
      }, parameters);
    };

    that.off = function (type, method) {
      var i, len;
      if (registry.hasOwnProperty(type)) {
        for (i = 0, len = registry[type].length; i < len; i++) {
          if (registry[type][i].method === method) {
            registry[type].splice(i, 1);
            break;
          }
        }
      }
      return this;
    };

    return that;
  };

} (window));
