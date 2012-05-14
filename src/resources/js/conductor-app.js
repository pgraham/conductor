/**
 * This javascript provides a basic shell for building a javascript web-app.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
(function ($, CDT, undefined) {
  "use strict";

  var tabs;

  CDT.app = {};
  eventuality(CDT.app);

  CDT.app.addView = function (id, lbl, elm) {
    if (tabs === undefined) {
      // TODO Queue the view to be added once the document is ready
      return;
    }

    elm.attr('id', id).addClass('cdt-app-view').appendTo(tabs);
    tabs.tabs('add', '#' + id, lbl);

    // Now that the tab has been added, it is guaranteed that there is a tab
    // nav element.  Only top needs to be set programtically, the rest of the
    // edges are set in css
    elm.css('top', tabs.find('.ui-tabs-nav').outerHeight(true)).layout();
  }

  CDT.app.addMessage = function (message, type, details) {
    if (details) {
      message += '<ul>';
      $.each(details, function (idx, val) {
        message += '<li>' + val;
      });
      message += '</ul>';
    }

    $('<div class="cdt-app-msg ui-corner-top"/>')
      .addClass(type)
      .html(message)
      .attr('title', 'Click to dismiss').tooltip()
      .appendTo($('body'))
      .css('opacity', 0.8)
      .css('zIndex', 1002)
      .hide()
      .slideDown()
      .click(function () {
        $(this).slideUp('fast', function () { $(this).remove(); });
      });
  };

  CDT.app.clearMessages = function () {
    $('.cdt-app-msg').slideUp('fast', function () {
      $(this).remove();
    });
  };

  $(document).ready(function () {
    var options, logout, viewSite, previewWnd;

    // Initialize the options menu for the app
    logout = $('<button>Logout</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        $.ajax({
          url: _p('/logout'),
          type: 'POST',
          success: function () {
            window.location.href = _p('/');
          }
        });
      });
    viewSite = $('<button>View Site</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        if (previewWnd === undefined || previewWnd.closed) {
          previewWnd = window.open(_p('/'), 'preview');
        } else {
          previewWnd.location.reload();
          previewWnd.focus();
        }
      });

    options = $('<div id="cdt-app-menu" />')
      .append(viewSite)
      .append(logout)
      .appendTo('body');

    // Initialize the tab panel that will contain the app
    tabs = $('<div id="cdt-app-container"><ul/></div>')
      .appendTo($('body'))
      .tabs()
      .bind('tabsshow', function (event, ui) {
        $(ui.panel).layout();
        CDT.app.fire({
          type: 'view-change',
          id: ui.panel.id,
          ui: ui
        });
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
      
      if (opts.dataType === 'json') {
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
