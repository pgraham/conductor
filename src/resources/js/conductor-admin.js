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
  var configuration_editor, configuration_form;

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

  configuration_form = function (model) {
    var that = eventuality({}), dialog;

    dialog = $('<div/>')
      .attr('title', model.name)
      .addClass('cdt-ConfigEditor')
      .append(
        $('<textarea />')
          .attr('name', 'config_value_' + model.id)
          .val(model.value)
      );

    that.close = function () {
      dialog.dialog('close');
      that.fire('close');
    };

    that.show = function () {
      dialog.dialog({
        modal: true,
        buttons: {
          "Save" : function () {
            var props = {}, input;

            input = dialog.find('textarea');

            props.id = model.id;
            props.name = model.name;
            props.value = input.val() !== '' ? input.val() : null;

            window['conductor_model_ConfigValueCrud'].update(props, that.close);
          }
        },
        width: 505
      });
    };

    return that;
  };

  if (CDT.admin === undefined) {
    CDT.admin = {};
  }

  CDT.admin.configuration_editor = configuration_editor;

})( jQuery, CDT );
