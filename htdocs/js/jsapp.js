CDT.ns('CDT.app');

/**
 * This javascript provides a basic shell for building a javascript web-app.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  var tabs, menu, msgManager;

  observable(CDT.app);

  CDT.app.addView = function (id, lbl, elm, closeable) {
    if (tabs === undefined) {
      // TODO Queue the view to be added once the document is ready
      return;
    }

    if ($('#' + id + '.ui-tabs-panel').length) {
      CDT.app.showView(id);
      return;
    }

    elm.attr('id', id).addClass('cdt-app-view').appendTo(tabs);
    tabs.tabs('add', '#' + id, lbl);

    if (closeable) {
      tabs.find('.ui-tabs-nav li').last().addClass('closeable')
        .append(
          $('<span class="ui-icon ui-icon-close">Remove Tab</span>')
            .addClass('ui-corner-all')
            .css({
              border: '1px solid transparent',
              cursor: 'pointer',
              margin: '1px 1px 0 0'
            })
            .click(function () {
              var tab = $(this).parent(),
                  tabH = tab.height(),
                  idx = tabs.find('.ui-tabs-nav li').index(tab);

              // Show the previous tab, or next tab if this is the first tab
              if (idx === 0) {
                tabs.tabs('select', idx + 1);
              } else {
                tabs.tabs('select', idx - 1);
              }
    
              tab.css({
                position: 'absolute',
                top: 'auto',
                bottom: $(window).height() - tab.offset().top - tabH,
                left: tab.offset().left
              }).animate({
                height: 0
              }, 'slow', 'easeOutCirc', function () {
                tabs.tabs('remove', idx);
              });

            })
            .mouseover(function () {
              $(this).css('border', '1px solid #000');
            })
            .mouseout(function () {
              $(this).css('border', '1px solid transparent');
            })
        );
    }

    // Now that the tab has been added, it is guaranteed that there is a tab
    // nav element so we can position the added tab.  Only top needs to be set
    // programtically, the rest of the edges are set in css
    elm.css('top', tabs.find('.ui-tabs-nav').outerHeight(true)).layout();
  };

  CDT.app.showView = function (id) {
    tabs.find('.ui-tabs-nav li').each(function (idx) {
      if ($(this).attr('aria-controls') === id) {
        tabs.tabs('select', idx);
        return false;
      }
    });
  };

  CDT.app.addMenuItem = function (item) {
    menu.append(item);
  }

  msgManager = (function () {
    var newMsg = false;

    return {
      addMessage: function (message, type, details) {
        newMsg = true;
        CDT.message(message, {
          type: type,
          details: details,
          autoRemove: type === 'info' ? 5000 : 0
        });

        // Fire a timeout to clean the new message flag so that any AJAX
        // requests not triggered durring the current execution frame will
        // clear the current messages.
        setTimeout(function () {
          newMsg = false;
        }, 10);
      },
      clearMessages: function () {
        // Only clear the messages if there is no new message.  A new message is
        // one that has been added durring the current execution frame.  This is
        // to prevent clearing the message of an AJAX response that triggers
        // another AJAX request
        if (!newMsg) {
          $('.cdt-msg').slideUp('fast', function () {
            $(this).remove();
          });
        }
      }
    };
  } ());

  CDT.app.addMessage = function (message, type, details) {
    msgManager.addMessage(message, type, details);
  };

  CDT.app.clearMessages = function () {
    msgManager.clearMessages();
  };

  $(document).ready(function () {
    menu = $('<div id="cdt-app-menu" />').appendTo('body');

    // Initialize the tab panel that will contain the app
    tabs = $('<div id="cdt-app-container"><ul/></div>')
      .appendTo($('body'))
      .tabs({
        fx: { opacity: 'toggle', duration: 200 }
      })
      .bind('tabsshow', function (event, ui) {
        $(ui.panel).layout();
        CDT.app.trigger('view-change', [ {
          id: ui.panel.id,
          ui: ui
        } ]);
      })
      .bind('tabsselect', function (event, ui) {
        CDT.app.clearMessages();
      });

    // Add global ajax handler to remove any messages before a new AJAX request
    // is made
    $('body').ajaxSend(function (e, xhr, opts) {
      CDT.app.clearMessages();
    });

    // Add global ajax handlers to display any messages.
    $('body').ajaxSuccess(function (e, xhr, opts) {
      var response, msg, msgType, elm;
      
      if (opts.dataType === 'json' ||
          xhr.getResponseHeader('Content-Type') === 'application/json')
      {
        response = $.parseJSON(xhr.responseText);
        if (!response || !response.msg) {
          return; 
        }

        if ($.isPlainObject(response.msg)) {
          msg = response.msg.text;
          msgType = response.msg.type;
        } else {
          msg = response.msg;
          if (response.success) {
            msgType = 'info';
          } else {
            msgType = 'error';
          }
        }

        CDT.app.addMessage(msg, msgType);
      }

    });

    $('body').ajaxError(function (e, xhr, opts, err) {
      var response, msg, msgs;

      if (xhr.status === 401) {
        // Request was unauthorized, reload the current page to request
        // authorization.
        // TODO This will need to be made smarter, possibly will need to build a
        //      login form dynamically and authorize async.
        window.location.reload();
        return;
      }

      if (opts.dataType === 'json') {
        response = $.parseJSON(xhr.responseText);

        if ($.isPlainObject(response)) {
          if (response.msg) {
            msg = response.msg;
          }

          if (response.msgs) {
            msgs = response.msgs;
          }

        } else if (typeof response === 'string') {
          msg = response;
        }

      }

      if (msg === undefined) {
        msg = xhr.status + ": " + xhr.statusText;
      }

      CDT.app.addMessage(msg, 'error', msgs);
    });
  });

} (jQuery.noConflict(), CDT));
