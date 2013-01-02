/**
 * This script defines a set of functions to easily add CSS classes to elements
 * on mouse events.
 *
 *  - `notifyover`: Add class when mouse is over set of matched elements
 *  - `notifypress`: Add class when mouse button is pressed over set of
 *                   matched elements.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, undefined) {

  var DEFAULT_OVER_CLASS = 'over',
      DEFAULT_PRESS_CLASS = 'press',

      toggleFns = {};

  function buildAddClass(cssClass) {
    return function () {
      $(this).addClass(cssClass);
    };
  }

  function buildRemoveClass(cssClass) {
    return function () {
      $(this).removeClass(cssClass);
    };
  }

  function buildToggleFunctions(cssClass) {
    toggleFns[cssClass] = {
      add: buildAddClass(cssClass),
      remove: buildRemoveClass(cssClass)
    };
  }

  function getAddClass(cssClass) {
    if (!toggleFns.hasOwnProperty(cssClass)) {
      buildToggleFunctions(cssClass);
    }
    return toggleFns[cssClass].add;
  }

  function getRemoveClass(cssClass) {
    if (!toggleFns.hasOwnProperty(cssClass)) {
      buildToggleFunctions(cssClass);
    }
    return toggleFns[cssClass].remove;
  }

  $.fn.notifyover = function (cssClass) {
    cssClass = cssClass || DEFAULT_OVER_CLASS;

    return this.each(function () {
      $(this).hover(getAddClass(cssClass), getRemoveClass(cssClass));
    });

  };

  $.fn.notifypress = function (cssClass) {
    cssClass = cssClass || DEFAULT_PRESS_CLASS;

    return this.each(function () {
      $(this).mousedown(getAddClass(cssClass));
      $(this).mouseup(getRemoveClass(cssClass));
      $(this).mouseleave(getRemoveClass(cssClass));
    });
  };

})( jQuery );
