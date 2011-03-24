/**
 * Conductor admin interface.
 */
(function ($, undefined) {
  var models   = ${json:models},
      menu, ctnt;

  var loadDashboard = function () {
    ctnt.empty().append($('<div/>')
      .text('Welcome, choose a menu item above to configure your site'));
  };

  ${each:editors as editor}
    ${editor}
  ${done}

  ${each:forms as form}
    ${form}
  ${done}

  $(document).ready(function () {
    menu = $('#menu ul');
    ctnt = $('#ctnt');

    menu.append(
      $('<li/>').append(
        $('<a href="#">Dashboard</a>')
          .click(function () {
            loadDashboard();
          })));

    ${each:modelNames as model}
      menu.append(
        $('<li/>').append(
          $('<a href="#">' + models['${model}'].name.plural + '</a>')
            .click(function () {
              $('#ctnt').empty().append((new ${model}Editor()).getElement());
            })));
    ${done}

    menu.menu();
    loadDashboard();
  });
} (jQuery));
