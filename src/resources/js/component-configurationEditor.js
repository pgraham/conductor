CDT.ns('CDT.cmp');
CDT.ns('CDT.model');
/**
 * Component for editing global configuration values.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */

/**
 * CDT.cmp.globalConfigEditor()
 */
(function ($, CDT, undefined) {
  "use strict";

  function addGlobalConfigValue(section, name, value) {
    section.append(
      $('<div class="cdt-config-value"/>')
        .append( $('<label/>').text(name.split('.').pop()) )
        .append(
          $('<div/>')
            .append( $('<span/>').text(value) )
            .append( 
              $('<button class="ui-button-small"/>')
                .button({
                  text: false,
                  icons: { primary: 'ui-icon-pencil' }
                })
                .click(function () {
                  var btnCtx = $(this).hide(),
                      valEl = btnCtx.prev(),
                      input = createValueEditor(name, valEl.text()); 

                  input.blur(function () {
                    btnCtx.show();
                    input.replaceWith(valEl);
                  });

                  valEl.replaceWith(input);
                  input.focus().get(0).select();
                })
            )
        )
    );
  }

  function buildToggleButton() {
    var toggle, path, collapsed = false;
    
    toggle = CDT.icon('caret-down')
    path = toggle.data('icon-path');

    toggle.data('icon-paper').rect(0, 0, 32, 32)
      .attr({ fill: '#000', opacity: 0 })
      .hover(function () {
        path.stop().animate({
          fill: '#999'
        }, 300);
      },
      function () {
        path.stop().animate({
          fill: '#000'
        }, 300);
      })
      .click(function () {
        if (!collapsed) {
          path.stop().animate({ transform: 'r-90' }, 200);
          toggle.parent().siblings('.cdt-config-section, .cdt-config-value')
            .slideUp('fast');
        } else {
          path.stop().animate({ transform: 'r0' }, 200);
          toggle.parent().siblings('.cdt-config-section, .cdt-config-value')
            .slideDown('fast');
        }
        collapsed = !collapsed;
      });

    return toggle;
  }

  function createValueEditor(name, value) {
    return $('<input type="text" class="text"/>')
      .addClass('ui-widget-content ui-corner-all')
      .val(value)
      .attr('title', "Press 'Enter' to save")
      .tooltip()
      .keyup(function (e) {
        var ctx = $(this);

        if (e.which === 13) {
          ctx.addClass('working');
          $.ajax({
            url: _p('/globalConfig/' + name),
            type: 'PUT',
            data: $(this).val(),
            processData: false,
            dataType: 'json',
            success: function () {
              ctx.parents('.global-config-editor') .trigger('global-config-updated');
            }
          });
        } else if (e.which === 27) {
          ctx.blur();
        }
      });

  }

  function getConfigValueSection(name, parent) {
    var depth, section, expand, collapse;

    if (parent) {
      parent.children('.cdt-config-section').each(function () {
        if ($(this).data('config-section-id') === name) {
          section = $(this);
          return false;
        }
      });

      if (section) {
        return section;
      }
    }

    // If we've made it here then either there was no parent specified so the
    // top level section needs to be created or the parent doesn't have a
    // section with the given name.
    depth = parent ? parent.data('config-section-depth') + 1 : 1;

    section =  $('<div class="cdt-config-section"/>')
      .append(
        $('<h' + depth + ' class="section-name"/>')
          .text(name)
          .append( buildToggleButton() )
      )
      .data('config-section-id', name)
      .data('config-section-depth', depth);

    if (parent) {
      parent.append(section);
    }

    return section;
  }

  function loadGlobalConfig(elm) {
    elm.working();
    $.ajax({
      url: _p('/globalConfig'),
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        elm.children('.cdt-config-section, .cdt-config-value').remove();
        $.each(data, function (idx, config) {
          var parts = config.name.split('.'),
              section = elm,
              i, len;

          parts.pop();
          for (i = 0, len = parts.length; i < len; i++) {
            section = getConfigValueSection(parts[i], section);
          }

          addGlobalConfigValue(section, config.name, config.value);
        });
      },
      complete: function () {
        elm.done();
      }
    });
  }

  CDT.cmp.globalConfigEditor = function () {
    var elm;

    // Create top level configuration section
    elm = $('<div class="global-config-editor"/>')
      .append($('<h1/>').text('Global Settings').prepend(CDT.icon('globe', {
        fill: '#999'
      })))
      .data('config-section-depth', 1);

    elm.on('global-config-updated', function () {
      loadGlobalConfig(elm);
    });

    // Defer loading until after the element has been added to the DOM.  This
    // will ensure the load mask is positioned correctly
    setTimeout(function () {
      loadGlobalConfig(elm);
    }, 10);

    return $.extend(elm, {
    });
  };

} (jQuery, CDT));

