(function ($, CDT, undefined) {
  "use strict";

  var current;

  CDT.dialog = function (ctnt) {
    var shadow, bg, elm, top, left, width, height;

    if (current) {
      current.remove();
    }

    shadow = $('<div class="ui-widget-shadow ui-corner-all"/>')
      .css({
        'position': 'absolute',
        'z-index': 5
      })
      .appendTo('body');
    bg = $('<div class="ui-corner-all"/>').appendTo('body');
    elm = $('<div class="ui-corner-all"/>').html(ctnt).appendTo('body');

    width = Math.round($(window).width() * 0.80);
    height = Math.round($(window).height() * 0.80);

    top = Math.round(($(window).height() - height) / 2);
    left = Math.round(($(window).width() - width) / 2);

    bg.width(width + 30).height(height + 30).css({
      'position': 'absolute',
      'top': top,
      'left': left,
      'z-index': 5,
      'background-color': '#222',
      'opacity': 0.8
    });

    elm.width(width).height(height).css({
      'position': 'absolute',
      'top': top,
      'left': left,
      'padding': 15,
      'z-index': 5
    });

    shadow
      .width(elm.outerWidth())
      .height(elm.outerHeight())
      .css({
        'top': top,
        'left': left
      });

    current = {
      remove: function () {
        shadow.fadeOut('fast');
        bg.fadeOut('fast');
        elm.fadeOut('fast', function () {
          elm.trigger('close');
          elm.remove();
          bg.remove();
          shadow.remove();
        });

        // Clear the current dialog
        current = undefined;
      }
    };

    elm.append($('<button/>')
      .css({
        'position': 'absolute',
        'top': 5,
        'right': 5
      })
      .button({
        icons: { primary: 'ui-icon-closethick' },
        text: false
      })
      .click(function () {
        current.remove();
      })
    ).layout('fillWith', elm.children().first());

    elm.hide().fadeIn();
    shadow.hide().fadeIn();

    return elm;
  };
} (jQuery, CDT));
