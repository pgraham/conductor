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
              ${model}_editor().appendTo($('#ctnt').empty());
            })));
    ${done}

    menu.menu();
    menu.find('li a').first().click();
  });
} (jQuery));
