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

  CDT.data.crudProxy = function (colName) {
    var baseUrl = _p('/' + colName);

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
          success: cb
        });
      },
      retrieveOne: function (id, cb) {
        $.ajax({
          url: baseUrl + '/' + id,
          type: 'GET',
          dataType: 'json',
          success: cb
        });
      },
      update: function (id, params, cb) {
        $.ajax({
          url: baseUrl + '/' + id,
          type: 'POST',
          data: { 'params': JSON.stringify(params) },
          dataType: 'json',
          success: cb
        });
      }
    };
  };

} (jQuery, CDT));
