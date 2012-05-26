/**
 * CDT.message
 *
 * A message widget which is added to the bottom of the page.
 */
(function ($, CDT, undefined) {

  CDT.message = function (message, type, details) {
    if (details) {
      message += '<ul>';
      $.each(details, function (idx, val) {
        message += '<li>' + val;
      });
      message += '</ul>';
    }

    return $('<div class="cdt-msg ui-corner-top"/>')
      .addClass(type)
      .html(message)
      .attr('title', 'Click to dismiss').tooltip()
      .appendTo($('body'))
      .css('opacity', 0.8)
      .hide()
      .click(function () {
        $(this).slideUp('fast', function () { $(this).remove(); });
      })
      .slideDown();
  };

} (jQuery, CDT));
