/*
 * Javascript part for assigning, retrieving and resetting a value from an
 * object.
 */
(function (exports, undefined) {
  "use strict";

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

} (window));
