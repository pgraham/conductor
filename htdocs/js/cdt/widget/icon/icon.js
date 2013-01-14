/**
 * CDT.icon
 */
(function (exports, $, R, undefined) {
  "use strict";

  var DEFAULT_OPTS, OUTLINE_OPTS;

  DEFAULT_OPTS = {
    width: 32,
    height: 32,
    fill: '#000',
    stroke: 'none',

    // An additional path that outlines the path is added and made available        // as the 'icon-outline' data attribute.  The outline is invisible by
    // default.  This can be controlled using the outline options.  See
    // OUTLINE_OPTS for supported options.
    outline: null
  };

  OUTLINE_OPTS = {
    stroke: '#fff',
    'stroke-width': 1,
    'stroke-linejoin': 'round',
    opacity: 0,
    showOnHover: false
  };
      
  $.widget('ui.icon', {
    options: $.extend({}, DEFAULT_OPTS),

    _create: function () {
      var self = this, tx, ty, sx, sy, outlineFadeInAnim, outlineFadeOutAnim;

      self.options.outline = $.extend({}, OUTLINE_OPTS, self.options.outline);

      self.element.addClass('cdt-icon');

      self.paper = R(self.element[0], self.options.width, self.options.height);

      // Add the path
      self.path = self.paper.path().attr(self.options);
      self.outline = self.paper.path(self.options.path)
        .attr(self.options.outline);

      // Calculate scale factor and necessary translation based on desired image
      // size.  Scale factor is based on original size of 32x32.
      tx = self.options.width / 2 - 16;
      ty = self.options.height / 2 - 16;
      sx = self.options.width / 32;
      sy = self.options.height / 32;

      self.transform = 't' + tx + ',' + ty + 's' + sx + ',' + sy;

      // Scale the path to the desired size and translate it to the middle of
      // the canvas
      // TODO Scale the path definition so that consumers don't need to worry
      //      about preserving this transformation when applying their own
      self.path.transform(self.transform);
      self.outline.transform(self.transform);

      // Add an invisible rectangle the same size as the canvas to allow
      // mouse events to have a bigger landing area.
      self.mouse = self.paper.rect(
        0,
        0,
        self.options.width,
        self.options.height
      );
      self.mouse.attr({ fill: '#000', opacity: 0 });

      // If the outline showOnHover option is true add a hover handler which
      // will fade in the outline path on hover
      if (self.options.outline.showOnHover) {
        outlineFadeInAnim = R.animation({ opacity: 1 }, 200);
        outlineFadeOutAnim = R.animation({ opacity: 0 }, 200);

        self.mouse.hover(
          function () {
            self.outline
              .stop(outlineFadeOutAnim)
              .animate(outlineFadeInAnim);
          },
          function () {
            self.outline
              .stop(outlineFadeInAnim)
              .animate(outlineFadeOutAnim);
          }
        );
      }

    self.element
      .data('icon-paper', self.paper)
      .data('icon-path', self.path)
      .data('icon-outline', self.outline)
      .data('icon-transform', self.transform) // Save original transform that is can 
                                         // be preserved when adding additional
                                         // transformations.
      .data('icon-mouse', self.mouse);
    },

    animate: function (attrs, duration, easing) {
      duration = duration || $.fx.speeds._default;
      if (!$.isNumeric(duration)) {
        if (duration === 'normal' || duration === 'default') {
          duration = '_default';
        }
        duration = $.fx.speeds[duration];
      }

      easing = easing || 'linear';

      if (!$.isPlainObject(attrs)) {
        attrs = { transform: self.transform + attrs };
      }

      self.path.animate(attrs, duration, easing);
      self.outline.animate(attrs, duration, easing);
    }
  });

  exports.icon = function (opts) {
    return $('<span/>').icon(opts || {});
  };

} (CDT.ns('CDT.widget'), jQuery, Raphael));
