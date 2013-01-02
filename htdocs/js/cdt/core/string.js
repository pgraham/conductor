/**
 * Add some helpful functions to the String prototype.
 */
(function (exports, undefined) {
  "use strict";

  exports.format = function () {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function (match, number) {
      return args[number] !== undefined
        ? args[number]
        : match;
    });
  };

  exports.ltrim = function () {
    return this.replace(/^\s+/, '');
  };

  exports.rtime = function () {
    return this.replace(/\s+$/, '');
  };

  exports.trim = function (chars) {
    return this.replace(/^\s+|\s+$/g, '');
  };

  exports.ucfirst = function () {
    return this.charAt(0).toUpperCase() + this.substr(1);
  };

} (String.prototype));
