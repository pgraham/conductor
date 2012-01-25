/**
 * Widget which interfaces with a CRUD service to provide a local store of
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

      items = {};
      loaded = false;

      srvc.retrieve(spf, function (response) {
        $.each(response.data, function (idx, item) {
          items[item[idProperty]] = item;
        });

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
      return items[id];
    };

    that.isLoaded = function () {
      return loaded;
    };

    return that;
  };

} (jQuery, CDT));
