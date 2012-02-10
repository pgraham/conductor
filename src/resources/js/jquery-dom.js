/**
 * This file contains functions for building DOM elements using jQuery.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
if (window['dom'] === undefined) {
  var dom = {};
}
(function ($, dom, undefined) {

  dom.button = function (text, click) {
    return $('<button />')
      .attr('type', 'button')
      .text(text)
      .click(click);
  };

  dom.table = function () {
    return $('<table />')
      .append($('<thead />'))
      .append($('<tbody />'))
      .append($('<tfoot />'))
  };

} (jQuery, dom));
