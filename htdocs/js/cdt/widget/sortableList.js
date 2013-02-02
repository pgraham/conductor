/**
 * Sortable list with automatic scrolling.
 */
(function (exports, $, undefined) {
  "use strict";

  $.widget('ui.sortableList', {
    widgetEventPrefix: 'sortlist',
    options: {
      itemIdProperty: 'id',
      itemLabelProperty: 'name',

      // Sortable options
      appendTo: 'body',
      connectWith: '.cdt-sortable-list',
      containment: 'body',
      cursor: 'move',
      distance: 5,
      handle: '.ui-icon',
      helper: 'clone',
      opacity: 0.3,
      placeholder: 'cdt-sortable-list-placeholder',
      tolerance: 'pointer'
    },

    _create: function () {
      var self = this;

      this.element
        .addClass('cdt-sortable-list')
        .sortable($.extend({}, this.options, {
          stop: function (e, ui) {
            self._trigger('drop', e, ui);
          },
          update: function (e, ui) {
            if (ui.sender || this !== ui.item.parent()[0]) {
              // This event is triggered either by receiving an item from
              // another list or by removing an item from this list.  This
              // element only reports the sort event for sorting within the
              // list.
              return;
            }

            self._trigger('sort', e, $.extend(ui, {
              newOrder: self._getOrder(ui.item.data('cdt-sortable-item'))
            }));
          },
          receive: function (e, ui) {
            self._trigger('move', e, $.extend(ui, {
              insertOrder: self._getOrder(ui.item.data('cdt-sortable-item'))
            }));
          }
        }));
    },

    _destroy: function () {
      this.element.removeClass('cdt-sortable-list');
    },

    addItems: function (items) {
      var self = this;
      $.each(items, function (idx, item) {
        $('<li/>')
          .attr('id', item[self.options.itemIdProperty] || '')
          .addClass('cdt-sortable-list-item')
          .data('cdt-sortable-item', item)
          .append(self._buildGrip())
          .append(item[self.options.itemLabelProperty] || '')
          .appendTo(self.element);
      });
    },

    clear: function () {
      this.element.empty();
    },

    removeItem: function (itemId) {
      this.element.children().each(function () {
        var item = $(this).data('cdt-sortable-item');
        if (item[this.options.itemIdProperty] === itemId) {
          $(this).remove();
          return false;
        }
      });
    },

    _buildGrip: function () {
      return $('<span/>')
        .addClass('ui-icon ui-icon-grip-dotted-vertical');
    },

    _getOrder: function (item) {
      var self = this, ordering, newOrder;

      ordering = self.element.sortable('toArray');
      $.each(ordering, function (idx) {
        if (item[self.options.itemIdProperty] === parseInt(this, 10)) {
          newOrder = idx;
          return false;
        }
      });

      return newOrder;
    }
  });

  exports.sortableList = function (opts) {
    var elm = $('<ul/>')
    elm.sortableList(opts || {});
    return elm;
  };

} (CDT.ns('CDT.widget'), jQuery));
