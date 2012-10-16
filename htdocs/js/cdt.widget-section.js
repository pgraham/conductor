/**
 * Widget that wraps an element in a div with a header to form a section in a
 * page.
 *
 * @component CDT.section
 * @param string hdr The header text for the section
 * @param jQuery ctnt The content to wrap in the section
 * @return object
 */
(function ($, CDT, undefined) {
  "use strict";

  CDT.section = function (hdr, ctnt) {
    var section, elm, head;

    head = $('<div class="cdt-section-head"/>')
      .addClass('ui-widget-header ui-corner-all')
      .append($('<span class="header-text"/>').text(hdr));

    elm = $('<div class="cdt-section"/>')
      .addClass('ui-widget-content ui-corner-all')
      .append(head)
      .append(ctnt.addClass('cdt-section-body'));

    section = {
      elm: elm,
      head: head
    };

    return section;
  };
} (jQuery, CDT));
