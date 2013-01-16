/**
 * jQuery UI widget to make any block element collapsible so that is will
 * only display a single line of text.
 *
 * TODO
 * Update this to always use another div for the expanded state.  This will make
 * supporting floated expansions easier.  When showing a floated expansion, the
 * expanded div will be positioned absolutely over the collapsed div and then
 * animated.  When showing a non-floated expansion, the expansion will be
 * placed in the DOM after the collapsed div and the collapsed div will be
 * positioned absolutely underneath the expansion.
 */
"use strict";
(function (exports, $, CDT, undefined) {

  function getBoxHeight(elm) {
    return elm.outerHeight(true) - elm.height();
  }

  $.widget('ui.collapsible', {
    options: {
      actions: {},
      collapseLbl: 'Collapse',
      collapseEasing: 'easeOutQuart',
      collapseTooltip: 'Collapse',
      expandEasing: 'easeInQuart',
      expandLbl: 'Expand',
      expandTooltip: 'Expand',
      maxHeight: 0,
      speed: $.fx.speeds._default
    },

    _create: function () {
      var self = this, actionButtons;

      self.element.addClass('cdt-collapsible-collapsed');
      self.expanded = $('<div class="cdt-collapsible-expanded"/>');

      self.element.wrapInner($('<div class="cdt-collapsible-content"/>'));
      self.content = self.element.find('.cdt-collapsible-content');

      self.expandBtn = $('<button/>')
        .attr('title', self.options.expandTooltip)
        .tooltip()
        .button({
          label: self.options.expandLbl,
          text: false,
          icons: { primary: 'ui-icon-arrowthick-1-se' }
        })
        .click(function () {
          $({})
            .queue(function (next) {
              self._beforeExpand(next);
            })
            .queue(function (next) {
              self._expand(next);
            })
            .queue(function (next) {
              self._afterExpand(next);
            });
        })
        .appendTo(self.element)
        .wrap('<div class="cdt-collapsible-actions"/>');

      self.collapseBtn = $('<button/>')
        .attr('title', self.options.collapseTooltip)
        .tooltip()
        .button({
          label: self.options.collapseLbl,
          text: false,
          icons: { primary: 'ui-icon-arrowthick-1-nw' }
        })
        .click(function () {
          $({})
            .queue(function (next) {
              self._beforeCollapse(next);
            })
            .queue(function (next) {
              self._collapse(next);
            })
            .queue(function (next) {
              self._afterCollapse(next);
            });
        })
        .appendTo(self.expanded)
        .wrap('<div class="cdt-collapsible-actions"/>');

      actionButtons = $();
      $.each(self.options.actions, function (idx) {
        actionButtons = actionButtons.add(
          $('<button/>')
            .attr('title', this.title || '')
            .tooltip()
            .button({
              lbl: idx,
              text: false,
              icons: this.icons
            })
            .click(this.handler)
        );
      });
      self.expanded.find('.cdt-collapsible-actions').append(actionButtons);

      if (!this.options.layoutTarget) {
        this.options.layoutTarget = this.element.parent();
      }
    },

    _destroy: function () {
      var actions = $('.cdt-collapsible-actions');

      actions.find('button').button('destroy').remove();
      actions.remove();

      this.expanded.remove();

      this.content.children().appendTo(this.element);
      this.content.remove();

      this.element.removeClass('cdt-collapsible-collapsed');
    },

    _refresh: function () {
      var self = this;
      self.expanded.children('.cdt-collapsible-content').remove();
      self.expanded.prepend(self.content.clone());
    },

    _beforeExpand: function (next) {
      var self = this;
      self.expandBtn.tooltip('close');
      self._refresh();
      self._trigger('beforeexpand');
      next();
    },

    _expand: function (next) {
      var self = this, initialH, targetH;

      // TODO Preserve scroll position while animating.
      initialH = self.element.height();
      targetH = self.expanded.insertAfter(self.element).height();
      targetH = Math.min(targetH, ( self._getMaxHeight() || targetH ));

      targetH -= getBoxHeight(self.expanded);

      if (self.options.floated) {
        self.expanded.css({
          position: 'absolute',
          top: self.element.position().top,
          left: self.element.position().left,
          width: self.element.width(),
          zIndex: 1
        });
      } else {
        self.element.css({
          position: 'absolute',
          top: self.element.position().top,
          visibility: 'hidden',
          zIndex: -1
        });
      }

      self.expanded.height(initialH).animate(
        { height: targetH },
        {
          duration: self.options.speed,
          easing: self.options.expandEasing,
          complete: next,
          step: function () {
            self._trigger('step');
          }
        }
      );
    },

    _afterExpand: function (next) {
      var self = this;

      // Undo manual height so that if the content changes the div will expand
      // as necessary
      self.expanded.height('');
      if (self._getMaxHeight()) {
        self.expanded.css({
          'max-height': self._getMaxHeight() - getBoxHeight(self.expanded),
          'overflow-y': 'auto',
          'overflow-x': 'auto'
        });
      }
      self._trigger('expand');
      next();
    },

    _beforeCollapse: function (next) {
      var self = this;
      self.collapseBtn.tooltip('close');
      self._trigger('beforecollapse');
      next();
    },

    _collapse: function (next) {
      var self = this, initialH, targetH;

      initialH = self.expanded.height();
      targetH = self.element.height();

      if (self._getMaxHeight()) {
        self.expanded.css({
          'max-height': '',
          'overflow-y': '',
          'overflow-x': ''
        });
      }

      self.expanded.height(initialH).animate(
        { height: targetH },
        {
          duration: self.options.speed,
          easing: self.options.collapseEasing,
          complete: next,
          step: function () {
            self._trigger('step');
          }
        }
      );
    },

    _afterCollapse: function (next) {
      var self = this;

      self.expanded.height('').detach();

      if (!self.options.floated) {
        self.element.css({
          position: '',
          top: '',
          visibility: '',
          zIndex: ''
        });
      }
      self._trigger('collapse');
      next();
    },

    _getMaxHeight: function () {
      var self = this;

      return $.isFunction(self.options.maxHeight)
        ? self.options.maxHeight()
        : self.options.maxHeight;
    }

  });

  exports.collapsible = function (opts) {
    $('<div/>').collapsible(opts || {});
  };

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

} (CDT.ns('CDT.widget'), jQuery, CDT));
