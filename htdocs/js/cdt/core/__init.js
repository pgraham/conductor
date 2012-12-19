/**
 * Initialization for the cdt Javascript package.
 */
window.CDT = {};
(function (exports, global, undefined) {
  "use strict";

  /**
   * Ensure the specified namespace exists and return it. If a second parameter
   * is provided it will be used to initialize the namespace.
   *
   * # Examples:
   *
   * - Create a nested namespace and all necessary parent scopes.
   *     var exports = CDT.ns('ns1.ns2')
   *     window.ns1 !== undefined; // true
   *     window.ns1.ns2 !== undefined; // true
   *     exports === window.ns1.ns2; // true
   *
   * - Create a new namespace using an initialization object.
   *     var exports = CDT.ns('ns1.ns2', { ilike: 'Cookies' });
   *     exports.ilike === 'Cookies'; // true
   *     window.ns1.ns2.ilike === 'Cookies'; // true
   */
  exports.ns = function (ns, obj) {
    var parts = ns.split('.'), o = global, i, len;

    for (i = 0, len = parts.length; i < len; i++) {
      if (!o[parts[i]]) {
        if (i === len - 1 && obj) {
          o[parts[i]] = obj;
        } else {
          o[parts[i]] = {};
        }
      }
      o = o[parts[i]];
    }

    return o;
  };

} (CDT, window));
