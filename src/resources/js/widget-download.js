CDT.ns('CDT.widget');

/**
 * CDT.download(path)
 *
 * Creates an invisible iframe that downloads the content at the given path.
 * The path response needs to include appropriate headers to trigger a download.
 *
 * TODO Error handling
 */
(function ($, CDT, undefined) {

  var frame;

  CDT.download = function (path) {
    if (!frame) {
      frame = $('<iframe/>')
        .css('display', 'none')
        .appendTo('body');
    }

    frame.attr('src', _p(path))
  };

} (jQuery, CDT));
