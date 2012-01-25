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

    elm
      .attr('id', id)
      .addClass('cdt-app-view')
      .appendTo(tabs);
    tabs.tabs('add', '#' + id, lbl);

    // Now that the tab has been added, it is guaranteed that there is a tab
    // nav element.  Only top needs to be set programtically, the rest of the
    // edges are set in css
    elm.css('top', tabs.find('.ui-tabs-nav').outerHeight());
  }

  CDT.app.addMessage = function (message, type) {
    $('<div class="cdt-app-msg ui-corner-top"/>')
      .addClass(type)
      .text(message)
      .appendTo($('body'))
      .css('opacity', 0.8)
      .hide()
      .slideDown();
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
        window['LoginService'].logout(function () {
          window.location.href = CDT.resourcePath('/index.php');
        });
      });
    viewSite = $('<button>View Site</button>').button()
      .addClass('ui-button-small')
      .click(function () {
        if (previewWnd === undefined || previewWnd.closed) {
          previewWnd = window.open(CDT.resourcePath('/index.php'), 'preview');
        } else {
          previewWnd.location.reload();
          previewWnd.focus();
        }
      });

    options = $('<div class="ui-widget-header"/>')
      .append(logout)
      .append(viewSite)
      .appendTo('body');

    // Initialize the tab panel that will contain the app
    tabs = $('<div id="cdt-app-container"><ul/></div>')
      .css({
        'position': 'absolute',
        'top': options.outerHeight(),
        'bottom': 0,
        'left': 0,
        'right': 0
      })
      .appendTo($('body'))
      .tabs()
      .bind('tabsshow', function (event, ui) {
        CDT.layout.doLayout();
        CDT.app.fire({
          type: 'view-change',
          id: ui.panel.id,
          ui: ui
        });
      })
      .bind('tabsselect', function (event, ui) {
        CDT.app.clearMessages();
      });

    // TODO Why isn't this working?  The class is removed as expected but is
    //      added back later
    tabs.children().filter('.ui-widget-header').removeClass('ui-corner-all');

    // Add global ajax handler to remove any messages before a new AJAX request
    // is made
    $('body').ajaxSend(function (e, xhr, opts) {
      CDT.app.clearMessages();
    });

    // Add global ajax handlers to display any messages.
    $('body').ajaxSuccess(function (e, xhr, opts) {
      var response, msg, msgType, elm;
      
      if (opts.dataType === 'json') {
        // TODO Allow types to be specified, default will be error
        response = $.parseJSON(xhr.responseText);
        if (!response || !response.msg) {
          return; 
        }

        if ($.isPlainObject(response.msg)) {
          msg = response.msg.text;
          msgType = response.msg.type;
        } else {
          msg = response.msg;
          msgType = 'error';
        }

        CDT.app.addMessage(msg, msgType);
      }

    });

    $('body').ajaxError(function (e, xhr, opts, err) {
      var response, msg, elm;

      if (opts.dataType === 'json') {
        response = $.parseJSON(xhr.responseText);

        if (response.msg) {
          msg = response.msg;
        }
      }

      if (msg === undefined) {
        msg = xhr.status + ": " + xhr.statusText;
      }

      CDT.app.addMessage(msg, 'error');
    });
  });

} (jQuery.noConflict(), CDT));
