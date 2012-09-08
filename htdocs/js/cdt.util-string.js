/**
 * Add some helpful functions to the String prototype.
 */
(function (exports, undefined) {
  "use strict";

  exports.trim = function (chars) {
    return this.replace(/^\s+|\s+$/g, '');
  }

  exports.ltrim = function () {
    return this.replace(/^\s+/, '');
  }

  exports.rtime = function () {
    return this.replace(/\s+$/, '');
  }

} (String.prototype));
