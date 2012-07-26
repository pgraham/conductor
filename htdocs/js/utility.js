// From Javascript: The Good Parts by Douglas Crockford
var eventuality = function (that) {
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

// Javascript part for assigning and retrieving a value from an object
// -----------------------------------------------------------------------------

// TODO Determine a more appropriate spot for this, or if it is even need at all
var hasValue = function (that, spec) {
  that = that || {};

  var reset = function () {
    that.setValue(spec.initial);
  }

  that.getValue = spec.getValue;
  that.setValue = spec.setValue;
  that.reset = reset;

  // Set the initial value
  reset();

  return that;
};

// Add trim, ltrim and rtrim functions to strings
(function () {
  "use strict";
  String.prototype.trim = function (chars) {
    return this.replace(/^\s+|\s+$/g, '');
  }

  String.prototype.ltrim = function () {
    return this.replace(/^\s+/, '');
  }

  String.prototype.rtime = function () {
    return this.replace(/\s+$/, '');
  }
}());

// Add a date function that converts a given date string or object representing
// a UTC time to localtime
Date.utcToLocal = function (utc) {
  var local = new Date();

  if (typeof utc === 'string') {
    utc = new Date(utc);
  }
  
  local.setUTCFullYear(utc.getFullYear());
  local.setUTCMonth(utc.getMonth());
  local.setUTCDate(utc.getDate());
  local.setUTCHours(utc.getHours());
  local.setUTCMinutes(utc.getMinutes());
  local.setUTCSeconds(utc.getSeconds());
  
  return local;
};
