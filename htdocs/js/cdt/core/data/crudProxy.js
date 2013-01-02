/**
 * Object which communicates with a server-side CRUD service to perform actions
 * on a collection of entities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  if (CDT.data === undefined) {
    CDT.data = {};
  }

  function buildErrorHandler(cb) {
    return function (jqXHR, textStatus, errorThrown) {
      cb({
        success: false,
        msg: {
          text: errorThrown,
          type: 'error'
        }
      });
    };
  }

  CDT.data.crudProxy = function (baseUrl) {
    var baseUrl = _p(baseUrl), cache = {};

    return {
      create: function (params, cb) {
        $.ajax({
          url: baseUrl,
          type: 'POST',
          data: JSON.stringify(params),
          contentType: 'application/json',
          processData: false,
          dataType: 'json',
          success: cb,
          error: buildErrorHandler(cb)
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
          error: buildErrorHandler(cb)
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
          }
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
          }
        });
      },
      update: function (id, params, cb) {
        // Add an ordering token to the request so the updates sent in
        // succession that arrive at the server in the wrong order will be
        // handled correctly, ie, the later one will arrive first and be
        // processed while the earlier one will arrive second and be ignored.
        params.cdtOrderToken = new Date().getTime();

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
          error: buildErrorHandler(cb)
        });
      }
    };
  };

} (jQuery, CDT));
