/**
 * Collapsible page section with a header.
 *
 * @component CDT.collapsible
 * @part CDT.section
 * @param string hdr The header text for the collapsible component.
 * @param jQuery ctnt The content to wrap in a collapsible section.
 * @return jQuery The wrapper.
 */
(function ($, CDT, undefined) {
  "use strict";

  CDT.collapsible = function (hdr, ctnt, opts) {
    var section, toggleBtn, toggle;

    opts = $.extend({
      collapsed: false
    }, opts);

    section = CDT.section(hdr, ctnt);

    toggle = function () {
      // To work properly with a fill layout, the body element needs to be
      // toggled, and height set to auto so that the layout can be applied to
      // what the container will be like after the slide.  Once the layout is
      // applied then the body is re-toggled and the slide animation is
      // performed
      section.elm.height('auto');
      ctnt.toggle();

      section.elm.toggleClass('cdt-expanded cdt-collapsed');
      CDT.layout.doLayout();

      ctnt.toggle();
      ctnt.slideToggle();
      $('span', this).toggleClass('ui-icon-carat-1-n ui-icon-carat-1-s');
    };
    
    toggleBtn = $('<button class="ui-button-small"/>')
      .button({
        icons: {
          primary: opts.collapsed ? 'ui-icon-carat-1-s' : 'ui-icon-carat-1-n'
        },
        text: false
      })
      .click(toggle);

    if (opts.collapsed) {
      section.elm.addClass('cdt-collapsed');
      ctnt.hide();
    } else {
      section.elm.addClass('cdt-expanded');
    }
    section.head.append(toggleBtn);

    section.toggle = function () {
      toggle();
      return this;
    };

    return section;
  };

} (jQuery, CDT));
