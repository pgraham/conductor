/**
 * jQuery UI widget which creates an in-place text input for allowing the user
 * to quickly input information as required.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {
  "use strict";

  $.widget('cdt.quickinput', {
    options: {
      width: null
    },

    _create: function () {
      var self = this, siblings;

      self.input = $('<input/>').addClass('cdt-quick-input');
      self._setupEvents();

      if (self.options.prompt) {
        self.input.attr('title', self.options.prompt).tooltip();
      }

      if (self.options.addClass) {
        self.input.addClass(self.options.addClass);
      }

    },

    _destroy: function () {
      this.input.remove();
    },

    _calculateWidth: function () {
      var w = this.options.width;
      if (w === null) {
        w = this.element.parent().width() -
            this.element.position().left -
            this.input.mbpWidth();
      }

      if (this.options.maxWidth) {
        w = Math.min(w, this.options.maxWidth);
      }

      return w;
    },

    _finished: function () {
      if (this.input.is('.detached')) {
        return;
      }
      this.input.addClass('detached');

      this.input.tooltip('disable').detach();
      this.element.fadeTo('fast', 1);
    },

    _setupEvents: function () {
      var self = this;
      self.input
        .blur(function (e) {
          self._trigger('escaped', e);
          self._finished();
        })
        .keyup(function (e) {
          if (e.which === KeyEvent.DOM_VK_RETURN) {
            self._trigger('entered', e, {
              value: $(this).val()
            });
            self._finished();
          } else if (e.which === KeyEvent.DOM_VK_ESCAPE) {
            self._trigger('escaped', e);
            self._finished();
          }
        });
    },
    
    open: function () {
      var self = this, w;

      self.input.appendTo('body');
      w = self._calculateWidth();
      self.input.width(w).position({
        my: 'left top',
        at: 'left top',
        of: self.element
      }).hide();


      $({})
        .queue(function (next) {
          // Hide the element but dont remove it from the document flow to
          // avoid elements jumping around
          self.element.fadeTo('fast', 0, next)
        })
        .queue(function (next) {
          self.input
            .tooltip('enable')
            .show()
            .focus()
            .outerHeight(self.element.outerHeight())
            .outerWidth(self.element.outerWidth())
            .animate({ width: w }, 'fast', next);
        });
    }
  });

} (jQuery));
