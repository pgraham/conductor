(function (exports, undefined) {

  function cacheItem(val) {
    var isMiss = !!val;

    return {
      isMiss: function () {
        return isMiss;
      },
      val: function () {
        return val;
      }
    };
  }

  exports.cache = function () {
    var cache = {};

    return {
      clear: function (key) {
        delete cache[key];
        return this;
      },
      get: function (key) {
        if (!cache[key]) {
          return cacheItem();
        }
        return cache[key];
      },
      put: function (key, val) {
        cache[key] = cacheItem(val);
        return this;
      }
    };
  };

} (CDT.ns('CDT.util')));
