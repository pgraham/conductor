/**
 * Make object observable with subtyped events.
 *
 * Event hierarchy is specified using dots (.)
 *
 *     observable({}).on('type.child.leaf', function () {});
 *
 * When an event of type `type.child.leaf` is triggered, then handlers for
 * events of type `type` and `type.child` will also be triggered.  Event
 * handlers registered for a deeper event type are considered to be "more
 * specific" since they will be triggered by fewer event types.  When an event
 * with a substype is triggered, more specific handlers will be invoked first.
 */
(function (exports, undefined) {
  "use strict";

  function eventRegistry() {
    var handlers = [], registry = {}, that = {};

    function removeHandler(handler) {
      var i, len;
      for (i = 0, len = handlers.length; i < len; i++) {
        if (handlers[i] === handler) {
          handlers.splice(i, 1);
          break;
        }
      }
    }

    that.addHandler = function (handler, subtypes) {
      if (subtypes.length === 0) {
        handlers.push(handler);
        return;
      }

      var basetype = subtypes.shift();
      if (!registry.hasOwnProperty(basetype)) {
        registry[basetype] = eventRegistry();
      }
      registry[basetype].addHandler(handler, subtypes);
    };

    that.getHandlers = function (subtypes) {
      if (subtypes.length === 0) {
        return [].concat(handlers);
      }
      
      var basetype = subtypes.shift();
      if (!registry.hasOwnProperty(basetype)) {
        return [].concat(handlers);
      }

      return registry[basetype].getHandlers(subtypes).concat(handlers);
    };

    that.removeHandler = function (handler, subtypes) {
      if (subtypes.length === 0) {
        removeHandler(handler);
        return;
      }

      registry[subtypes.shift()].removeHandler(handler, subtypes);
    };

    return that;
  }

  exports.observable = function (that) {
    var registry = eventRegistry();

    that.trigger = function (event, data) {
      var handlers = registry.getHandlers(event.split('.')), i, len;
      for (i = 0, len = handlers.length; i < len; i++) {
        handlers[i].apply(this, [ { type: event } ].concat(data));
      }
      return this;
    };

    that.on = function (type, handler) {
      var events = type.split(' '), i, len;

      for (i = 0, len = events.length; i < len; i++) {
        registry.addHandler(handler, events[i].split('.'));
      }
      return this;
    };

    that.one = function (type, handler) {
      var wrapper = function () {
        handler.apply(this, arguments);
        that.off(type, wrapper);
      };
      that.on(type, wrapper);
    };

    that.off = function (type, handler) {
      var events = type.split(' '), i, len;

      for (i = 0, len = events.length; i < len; i++) {
        registry.removeHandler(handler, events[i].split('.'));
      }
      return this;
    };

    return that;
  };

  /**
   * Convenience function for creating an empty observable object that can be
   * used as an EventBus.
   */
  exports.eventRegistry = function () {
    return observable({});
  };

} (window));
