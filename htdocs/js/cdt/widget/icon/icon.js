/**
 * CDT.icon
 */
(function (exports, $, R, undefined) {
  "use strict";

  var DEFAULT_OPTS, OUTLINE_OPTS;

  DEFAULT_OPTS = {
    width: 32,
    height: 32,
    canvasWidth: 32,
    canvasHeight: 32,
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

      self.pathStrs = self.options.paths;
      if (!$.isPlainObject(self.pathStrs)) {
        self.pathStrs = { path: self.pathStrs };
      }
      self.options.outline = $.extend({}, OUTLINE_OPTS, self.options.outline);

      self.element.addClass('cdt-icon');
      self.paper = R(
        self.element[0],
        self.options.canvasWidth,
        self.options.canvasHeight
      );

      // Calculate scale factor and necessary translation based on desired image
      // size.  Scale factor is based on original size of 32x32.
      tx = self.options.width / 2 - 16;
      ty = self.options.height / 2 - 16;
      sx = self.options.width / 32;
      sy = self.options.height / 32;

      // Add the path
      self.paths = {};
      self.outlines = {};
      $.each(self.pathStrs, function (idx) {
        var pstr = this;

        pstr = CDT.util.raphael.scalePathString(pstr, sx, sy);
        // TODO Move path box into center of canvas
        //pstr = CDT.util.raphael.translatePathString(pstr, tx, ty);

        self.paths[idx] = self.paper.path(pstr).attr(self.options);
        self.outlines[idx] = self.paper.path(pstr).attr(self.options.outline);
      });

      // Add an invisible rectangle the same size as the canvas to allow
      // mouse events to have a bigger landing area.
      self.mouse = self.paper.rect(
        0,
        0,
        self.options.canvasWidth,
        self.options.canvasHeight
      );
      self.mouse.attr({ fill: '#000', opacity: 0 });

      // If the outline showOnHover option is true add a hover handler which
      // will fade in the outline path on hover
      if (self.options.outline.showOnHover) {
        outlineFadeInAnim = R.animation({ opacity: 1 }, 200);
        outlineFadeOutAnim = R.animation({ opacity: 0 }, 200);

        self.mouse.hover(
          function () {
            $.each(self.pathStrs, function (idx) {
              self.outlines[idx]
                .stop(outlineFadeOutAnim)
                .animate(outlineFadeInAnim);
            });
          },
          function () {
            $.each(self.pathStrs, function (idx) {
              self.outlines[idx]
                .stop(outlineFadeInAnim)
                .animate(outlineFadeOutAnim);
            });
          }
        );
      }

    self.element
      .data('icon-paper', self.paper)
      .data('icon-paths', self.paths)
      .data('icon-outlines', self.outlines)
      .data('icon-mouse', self.mouse);
    },

    animate: function (attrs, duration, easing) {
      var self = this;

      $.each(self.pathStrs, function (idx) {
        self.animatePath(idx, attrs, duration, easing);
      });
    },

    animatePath: function (path, attrs, duration, easing) {
      var self = this;

      duration = duration || $.fx.speeds._default;
      if (!$.isNumeric(duration)) {
        if (duration === 'normal' || duration === 'default') {
          duration = '_default';
        }
        duration = $.fx.speeds[duration];
      }

      easing = easing || 'linear';

      if (!$.isPlainObject(attrs)) {
        attrs = { transform: attrs };
      }

      self.paths[path].animate(attrs, duration, easing);
      self.outlines[path].animate(attrs, duration, easing);
    },

    pathAttrs: function (attrs) {
      var self = this;

      $.each(self.pathStrs, function (idx) {
        self.paths[idx].attr(attrs);
      });

      if (attrs.outline) {
        self.outlineAttrs(attrs.outline);
      }
    },

    outlineAttrs: function (attrs) {
      var self = this;

      $.each(self.pathStrs, function (idx) {
        self.outlines[idx].attr(attrs);
      });
    },

    transform: function (tstr) {
      var self = this;

      $.each(self.pathStrs, function (idx) {
        self.paths[idx].transform(tstr);
        self.outlines[idx].transform(tstr);
      });
    }
  });

  exports.icon = function (opts) {
    return $('<span/>').icon(opts || {});
  };

} (CDT.ns('CDT.widget'), jQuery, Raphael));
