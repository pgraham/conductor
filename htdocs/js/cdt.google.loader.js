(function ($, API, global, undefined) {

  jsapi = $.getScript('http://www.google.com/jsapi');

  API.load = function (api, version, opts) {
    var dfd = $.Deferred();

    if (opts.callback) {
      // If a callback has been specified in the options add it to the deferred
      // queue so that is doesn't get cloberred when the callback to resolve
      // the deferred is added below.
      dfd.done(opts.callback);
    }

    // TODO - Not all APIs support a callback parameter
    opts = $.extend(opts, {
      callback: function () {
        dfd.resolve();
      }
    });

    jsapi.done(function () {
      google.load(api, version, opts);
    });

    return dfd.promise();
  };

} (jQuery, CDT.ns('CDT.gapi'), window));
