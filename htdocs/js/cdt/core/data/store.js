/**
 * Object which interfaces with a CDT.data.CrudProxy to provide a local store of
 * model instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  if (CDT.data === undefined) {
    CDT.data = {};
  }

  CDT.data.createStore = function (srvc, idProperty) {
    var that, loaded, items, total, addHandler;

    idProperty = idProperty || 'id';

    that = observable({});

    // Override the on() method to immediately invoke any added load handlers if
    // the store is already loaded
    addHandler = that.on;
    that.on = function (type, handler) {
      addHandler(type, handler);
      if (type === 'load' && loaded) {
        handler.apply(this, [ 'load', { items: items, total: total } ]);
      }
      return this;
    };

    that.load = function (spf, cb) {
      spf = spf || {};
      cb = cb || $.noop;

      loaded = false;

      that.trigger('beforeload');
      srvc.retrieve(spf, function (response) {
        var e;
        items = response.data;
        total = response.total;

        loaded = true;

        e = { items: items, total: total };
        cb.apply(that, [ e ]);
        that.trigger('load', [ e ]);
      });
    };

    that.get = function (id) {
      if (id === undefined) {
        return items;
      }

      $.each(items, function (idx, item) {
        if (item[idProperty] === id) {
          return item;
        }
      });
      return null;
    };

    that.isLoaded = function () {
      return loaded;
    };

    return that;
  };

} (jQuery, CDT));
