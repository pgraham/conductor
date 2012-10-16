/**
 * Cross-browser dynamic stylesheet loader.
 */
(function ($, wnd, undefined) {
  "use strict";

  function loadCss(path) {
    if (document.createStyleSheet) {
      document.createStyleSheet(path);
    } else {
      $('<link/>')
        .attr('rel', 'stylesheet')
        .attr('href', path)
        .appendTo('head');
    }
  }

  wnd.loadCss = loadCss;
} (jQuery, window));
