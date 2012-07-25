/**
 * CDT.message
 *
 * A message widget which is added to the bottom of the page.
 */
(function ($, CDT, undefined) {
  
  var DEFAULT_OPTS = {
    type: 'info',
    details: null,
    autoRemove: 0
  };

  CDT.message = function (message, opts) {
    var elm, autoRemove;

    opts = $.extend({}, DEFAULT_OPTS, opts);

    if (opts.details) {
      message += '<ul>';
      $.each(details, function (idx, val) {
        message += '<li>' + val;
      });
      message += '</ul>';
    }

    // Remove current message.
    // TODO Allow stacking
    $('.cdt-msg').slideUp('fast', function () { $(this).remove(); });

    elm = $('<div class="cdt-msg ui-corner-top"/>')
      .addClass(opts.type)
      .html(message)
      .attr('title', 'Click to dismiss').tooltip()
      .appendTo($('body'))
      .css('opacity', 0.8)
      .hide()
      .click(function () {
        $(this).slideUp('fast', function () { $(this).remove(); });
        if (autoRemove) {
          clearTimeout(autoRemove);
        }
      })
      .slideDown();

    if (opts.autoRemove) {
      autoRemove = setTimeout(function () {
        elm.slideUp('fast', function () { elm.remove(); });
      }, opts.autoRemove);
    }

    return elm;
  };

} (jQuery, CDT));
