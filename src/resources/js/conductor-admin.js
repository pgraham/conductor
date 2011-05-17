/**
 * Conductor admin interface.
 */
(function ($, undefined) {
  var models = ${json:models},
      menu, ctnt, editor,
      i, len;

  ${each:editors as editor}
    ${editor}
  ${done}

  ${each:forms as form}
    ${form}
  ${done}

  $(document).ready(function () {
    menu = $('#menu ul');
    ctnt = $('#ctnt');

    ${each:modelNames as model}
      menu.append(
        $('<li/>').append(
          $('<a href="#">' + models['${model}'].name.plural + '</a>')
            .click(function () {
              ${model}_editor().appendTo(ctnt.empty());
            })
        )
      );
    ${done}

    // TODO - Only show the configuration link if the site defines configuration
    //        options
    menu.append(
      $('<li/>')
        .attr('id', 'config-menu-item')
        .append(
          $('<a href="#">Configuration</a>')
            .click(function () {
              CDT.admin.configuration_editor().appendTo(ctnt.empty());
            })
        )
    );

    menu.menu();
    menu.find('li a').first().click();
  });
} (jQuery));

(function ($, CDT, undefined) {
  var configuration_editor;

  configuration_editor = function () {
    var that;

    that = CDT.modelCollectionGrid({
      cols : [
        { 'id' : { 'field' : 'name', 'html' : true }, 'lbl' : 'Name' },
        { 'id' : { 'field' : 'value', 'html' : true }, 'lbl' : 'Value' }
      ],
      crudService : window.conductor_model_ConfigValueCrud,
      idProperty  : 'id',
      dataitem    : {},
      buttons     : {
        "Edit" : function () {
          var model = that.getSelected().pop();

          if (model !== undefined) {
            configuration_form(model).on('close', function () {
              that.refresh();
            }).show();
          }
        }
      }
    });

    return that;
  };

  if (CDT.admin === undefined) {
    CDT.admin = {};
  }

  CDT.admin.configuration_editor = configuration_editor;

})( jQuery, CDT );
