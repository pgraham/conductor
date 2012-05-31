/**
 * This widget creates a floating menu that can be positioned anywhere on the
 * page.
 *
 * @params position: config object passed to jQuery UI position() function to 
 *   attach the menu to an element.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {

  CDT.floatingmenu = function (cfg) {
    var elm;

    elm = $('<ul class="cdt-btn-menu ui-widget ui-widget-content">')
      .addClass('ui-corner-all')
      .css('position', 'absolute')
      .css('zIndex', 1)
      .hide()
      .appendTo('body');

    return $.extend(elm, {
      addMenuItem: function (text, onClick) {
        elm.append($('<li class="cdt-btn-menu-item ui-widget"/>')
          .text(text)
          .click(function (e) {
            onClick(e);
            elm.toggleMenu();
          })
          .mouseover(function () { $(this).addClass('ui-state-hover'); })
          .mouseout(function () { $(this).removeClass('ui-state-hover'); })
        );
        return elm;
      },
      toggleMenu: function () {
        if (elm.is(':visible')) {
          elm.slideUp('fast');
        } else {
          elm.show().position(cfg.position).hide()
            .slideDown('slow', 'easeOutCubic');
        }
      }
    });
  };

} (jQuery, CDT));
