/**
 * Input widget for a one-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
var ${model}_${relationship}_input = function (model) {
  var that, grid, selected;

  if (model !== null) {
    selected = function (request, response) {
      var spf = $.extend({
        filter: {
          ${rhsColumn}: model.${lhsIdProperty}
        }
      }, request);
      window['${rhsCrudService}'].retrieve(
        spf, CDT.gridIdCallback(response, '${rhsIdProperty}'));
    };
  } else {
    selected = [];
  }

  grid = CDT.modelDualGrid({
    nameProperty : '${nameProperty}',
    dataitem     : typeof ${rhs} === 'function' ? ${rhs}() : {},
    selected     : selected,
    available    : function (request, response) {
      var spf = $.extend({
        filter: {
          ${rhsColumn}: null
        }
      }, request);
      window['${rhsCrudService}'].retrieve(
        spf, CDT.gridIdCallback(response, '${rhsIdProperty}'));
    }
  });

  that = {
    elm: grid.elm,
    name: '${relationship}',
    lbl:  '${label}'
  };

  hasValue(that, {
    initial: (model !== null) ? model.${relationship} : [],
    getValue: function () {
      var selitems = $.ui.datastore.main.get( grid.selected.type ), value = [],
        i, len;

      for (i = 0, len = selitems.options.items.length; i < len; i += 1) {
        value.push(selitems.options.items[i].options.data.guid);
      }

      return value;
    },
    setValue: function (val) {
      grid.available.refresh();
      grid.selected.refresh();
    }
  });

  return that;
}
