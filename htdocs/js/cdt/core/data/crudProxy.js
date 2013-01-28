/**
 * Object which communicates with a server-side CRUD service to perform actions
 * on a collection of entities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function (exports, $, undefined) {
  "use strict";

  exports.crudProxy = function (baseUrl) {
    var baseUrl = _p(baseUrl), cache = {};

    return {
      cache: cache,
      create: function (params, cb) {
        $.ajax({
          url: baseUrl,
          type: 'POST',
          data: JSON.stringify(params),
          contentType: 'application/json',
          processData: false,
          dataType: 'json',
          success: cb,
          error: CDT.data.buildAjaxErrorHandler(cb)
        });
      },
      remove: function (id, cb) {
        $.ajax({
          url: baseUrl + '/' + id,
          type: 'DELETE',
          dataType: 'json',
          success: function () {
            delete cache[id];
            cb({ success: true });
          },
          error: CDT.data.buildAjaxErrorHandler(cb)
        });
      },
      retrieve: function (spf, cb) {
        $.ajax({
          url: baseUrl,
          type: 'GET',
          data: { 'spf' : JSON.stringify(spf) },
          dataType: 'json',
          success: function (response) {
            $.each(response.data, function (idx, entity) {
              cache[entity.id] = entity;
            });
            cb(response);
          },
          error: CDT.data.buildAjaxErrorHandler(cb)
        });
      },
      retrieveOne: function (id, cb) {
        if (cache[id]) {
          setTimeout(function () {
            cb(cache[id]);
          }, 10);
          return;
        }
        $.ajax({
          url: baseUrl + '/' + id,
          type: 'GET',
          dataType: 'json',
          success: function (response) {
            cache[response.id] = response;
            cb(response);
          },
          error: CDT.data.buildAjaxErrorHandler(cb)
        });
      },
      update: function (id, params, cb) {
        // Add an ordering token to the request so that updates that are sent in
        // quick succession and arrive at the server in the wrong order will be
        // handled correctly, ie, the later one will arrive first and be
        // processed while the earlier one will arrive second and be ignored.
        params.__ROT = new Date().getTime();

        $.ajax({
          url: baseUrl + '/' + id,
          type: 'POST',
          data: JSON.stringify(params),
          contentType: 'application/json',
          processData: false,
          dataType: 'json',
          success: function (response) {
            delete cache[id];
            cb(response);
          },
          error: CDT.data.buildAjaxErrorHandler(cb)
        });
      }
    };
  };

} (CDT.ns('CDT.data'), jQuery));
