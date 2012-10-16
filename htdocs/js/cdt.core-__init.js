/**
 * Initialization for the cdt Javascript package.
 */
window.CDT = {};
(function (exports, global, undefined) {
  "use strict";

  /**
   * Ensure the specified namespace exists and return it.
   *
   * `CDT.ns('ns1.ns2')` would ensure that window.ns1.ns2 exists and would
   * return it.
   */
  exports.ns = function (ns) {
    var parts = ns.split('.'), o = global, i, len;

    for (i = 0, len = parts.length; i < len; i++) {
      o[parts[i]] = o[parts[i]] || {};
      o = o[parts[i]];
    }

    return o;
  };

} (CDT, window));
