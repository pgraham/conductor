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

  CDT.data.crudProxy = function (baseUrl) {
    var baseUrl = _p(baseUrl), cache = {};

    return {
      create: function (params, cb) {
        $.ajax({
          url: baseUrl,
          type: 'POST',
          data: { 'params': JSON.stringify(params) },
          dataType: 'json',
          success: cb
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
          error: function (jqXHR, textStatus, errorThrown) {
            cb({ success: false });
          }
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
        $.ajax({
          url: baseUrl + '/' + id,
          type: 'POST',
          data: { 'params': JSON.stringify(params) },
          dataType: 'json',
          success: function (response) {
            delete cache[id];
            cb(response);
          }
        });
      }
    };
  };

} (jQuery, CDT));
