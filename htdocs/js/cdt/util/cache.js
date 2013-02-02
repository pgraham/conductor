(function (exports, jQuery, undefined) {

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
      each: function (fn) {
        $.each(cache, fn);
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
      },
      values: function () {
        var vals = [];
        this.each(function (idx) {
          if (!this.isMiss()) {
            vals.push(this.val());
          }
        });
        return vals;
      }
    };
  };

} (CDT.ns('CDT.util'), $));
