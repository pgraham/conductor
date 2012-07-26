/**
 * This file contains functions for building DOM elements using jQuery.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
if (window['CDT'] === undefined) {
  var CDT = {};
}
if (CDT.DOM === undefined) {
  CDT.DOM = {};
}

(function ($, DOM, undefined) {
  "use strict";

  DOM.button = function (text, click) {
    return $('<button />')
      .attr('type', 'button')
      .text(text)
      .click(click);
  };

  DOM.table = function () {
    return $('<table />')
      .append($('<thead />'))
      .append($('<tbody />'))
      .append($('<tfoot />'))
  };

  DOM.textInput = function (name) {
    return $('<input/>')
      .attr('type', 'text')
      .attr('name', name);
  }

} (jQuery, CDT.DOM));