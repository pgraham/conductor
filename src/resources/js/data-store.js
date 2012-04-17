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
    var that, loaded, items, addHandler;

    idProperty = idProperty || 'id';

    that = {};
    eventuality(that);

    // Override the on() method to immediately invoke any added load handlers if
    // the store is already loaded
    addHandler = that.on;
    that.on = function (type, method, parameters) {
      var func = typeof method === 'string'
        ? this[method]
        : method;

      addHandler(type, method, parameters);
      if (type === 'load' && loaded) {
        func.apply(this, parameters || [ { type: 'load', items: items } ]);
      }
      return this;
    };

    that.load = function (spf) {
      spf = spf || {};

      loaded = false;

      srvc.retrieve(spf, function (response) {
        items = response.data;

        loaded = true;
        that.fire({
          type: 'load',
          items: items,
          total: response.total
        });
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
