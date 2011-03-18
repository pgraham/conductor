/**
 * Conductor admin interface.
 */
(function ($) {

  /*
   * ===========================================================================
   * Menu Object
   * ===========================================================================
   */
  function Menu(sel) {
    this.elm = $(sel).get(0);
  }

  Menu.prototype.addItem = function (link, onClick) {
    $(this.elm).append(
      $('<li/>').append($('<a href="#">' + link + '</a>').click(onClick))
    );
  };

  Menu.prototype.ready = function () {
    $(this.elm).menu();
  };

  /*
   * ===========================================================================
   * Content Object
   * ===========================================================================
   */

  function Content(sel) {
    this.elm = $(sel).get(0);
  }

  Content.prototype.setContent = function (ctnt) {
    $(this.elm).empty().append(ctnt.getElement());
  };

  /*
   * ===========================================================================
   * Base Widget
   * ===========================================================================
   */

  function BaseView(elm) {
    this._elm = elm;
  }

  BaseView.prototype = {
    _elm: null,

    getElement: function () {
      return this._elm;
    }
  };

  /*
   * ===========================================================================
   * Dashboard Object
   * ===========================================================================
   */

  function Dashboard() {
    BaseView.call(this, this.build());
  }

  Dashboard.prototype = $.extend({}, new BaseView(), {
    build: function () {
      return $('<div/>').text('Welcome, choose a menu item above to' +
        ' configure your site').get(0);
    }
  });

  /*
   * ===========================================================================
   * ModelEditor Object
   * ===========================================================================
   */

  function ModelEditor(model) {
    BaseView.call(this, this.build(model.name.plural));
    this.model = model;

    window[model.crudService].get(/*new Filters(), 0, 0, function (data) {
      console.log(data);
    }*/);
  }

  ModelEditor.prototype = $.extend({}, new BaseView(), {
    build: function (pluralModelName) {
      return $('<div/>').text('Manage ' + pluralModelName).get(0);
    }
  });

  /*
   * ===========================================================================
   * The wiring
   * ===========================================================================
   */

  var CDT = function () {

    var menu;
    var ctnt;

    return {
      init: function () {
        var models = ${json:models},
            numModels = models.length,
            i;

        // Initiate menu
        menu = new Menu('#menu ul');
        menu.addItem('Dashboard', function () {
          ctnt.setContent(new Dashboard());
        });

        for (i = 0; i < numModels; i++) {
          (function (model) {
            menu.addItem(model.name.plural, function () {
              ctnt.setContent(new ModelEditor(model));
            });
          }(models[i]));
        }
        menu.ready();

        // Initiate the content area
        ctnt = new Content('#ctnt');
        ctnt.setContent(new Dashboard());
      }
    };
  }();

  $(document).ready(function () {
    CDT.init();
  });
}(jQuery));
