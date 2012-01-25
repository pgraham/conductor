/**
 * This script provides support for the Conductor framework.
 * It is a compiled script.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @copyright Philip Graham, 2011.  All rights reserved.
 */
var CDT = {};

(function ($, CDT, undefined) {

  var curPage, curPageSel, loadPage,

      /* Function for initializing the client */
      init,

      /* Function for retrieving the base path for resources */
      resourcePath,

      /*
       * Function to set the current page id, this should only be called on
       * startup.
       */
      setPageId;

  // Make CDT observable
  eventuality(CDT);

  /* Internal function for accessing the loadPage RPC */
  loadPage = function (pageId, sel) {
    $(sel).working();
    ConductorService.loadPage(pageId, function (data) {
      curPage = pageId;
      curPageSel = sel;

      $(sel).html(data).done();
      CDT.fire({
        type   : 'pageLoad',
        pageId : pageId
      });
    });
  };

  CDT.loadPage = function (pageId, sel) {
    history.pushState({
      pageId : pageId,
      sel    : sel
    }, '', window.location.href);

    loadPage(pageId, sel);
  };

  // Bind to back, forward buttons to load the requested page
  $(window).bind('popstate', function (e) {
    var state = e.originalEvent.state;
    if (state) {
      loadPage(state.pageId, state.sel);
    } else {
      // This is an initial page load
      history.replaceState({
        pageId : curPage,
        sel    : curPageSel
      });
    }
  });

  /*
   * Function for creating a path to a resource. This path will append the
   * site root to the given path.
   */
  resourcePath = function (path) {
    ${if:rootPath = /}
      return path;    
    ${else}
      return '${rootPath}' + path;
    ${fi}
  }
  CDT.resourcePath = resourcePath;

  CDT.checkResponse = function (response) {
    if (!response || response.msg === undefined) {
      return true;
    }

    if ($.isPlainObject(response.msg)) {
      return response.msg.type !== 'error';
    }

    // A message without a type is by default an error message.
    return false;
  };

  setPageId = function (curPageId, sel) {
    if (curPageId === undefined) {
      return;
    }
    curPage = curPageId;
    curPageSel = sel;

    CDT.fire({
      type   : 'page-load',
      pageId : curPageId
    });
  };

  /**
   * Initialize the conductor client.  This function is called automatically
   * and clobbers itself so that it can't be called twice.
   */
  init = function (curPageId, sel) {
    setPageId(curPageId, sel);

    // Add live click handler for the login form.
    $('#login.async').find('.cdt-Submit').live('click', function () {
      var form     = $('#login.async'),
          username = form.find('input[name="uname"]'),
          password = form.find('input[name="pw"]'),
          error    = form.find('.cdt-Error');

      error.empty();
      
      ConductorService.login(username.val(), password.val(), function (resp) {
        if (resp.msg === null) {
          loadPage(curPage, curPageSel);
        } else {
          error.text(resp.msg);
          username.focus().select();
        }
      });
    });

    // Don't let initialization happen twice but still allow the initial page
    // to be set
    CDT.init = setPageId;
  };
  CDT.init = init;

} (jQuery, CDT));

$(document).ready(function () {
  CDT.init();
});

// A jQuery extension for adding a load mask to any element
(function ( $ ) {

  $.fn.working = function () {

    return this.each(function () {
      var ctx = $(this), mask = ctx.data('working-mask');

      if (mask === undefined) {
        mask = $('<div/>')
          .addClass('ui-widget-overlay')
          .css('opacity', '0.65')
          .append(
            $('<img/>')
              .css('position', 'absolute')
              .css('opacity', '1')
              .offset({
                top: (ctx.height() / 2) - (${imgHeight} / 2),
                left: (ctx.width() / 2) - (${imgWidth} / 2)
              })
              .width(${imgWidth})
              .height(${imgHeight})
              .attr('src', '${targetPath}/img/working.gif'));

        ctx.data('working-mask', mask);
      }

      mask.appendTo(ctx);
    });
  };

  $.fn.done = function () {

    return this.each(function () {
      var ctx = $(this), mask = ctx.data('working-mask');

      if (mask !== undefined) {
        mask.detach();
      }
    });
  };

})( jQuery );

// A jQuery extension that adds a helpful bar for internet explorer users.
//
// Usage:
// ------
//
// $(document).ieBar();
(function ( $ ) {

  $.fn.ieBar = function () {

    if (!$.browser.msie) {
      return this;
    }

    return this.each(function () {
      var ctx = $(this), bar = ctx.data('ieBar');  

      if (bar === undefined) {
        bar = $('<div/>')
          .addClass('ieBar')
          .css({
            "position" : 'fixed',
            "left"     : '0',
            "right"    : '0',
            "bottom"   : '0',
            "height"   : '35px',
            "background-color" : '#AAA'
          });

        ctx.data('ieBar', bar);
      }

      bar.appendTo(ctx).fadeIn('slow');
    });
  };

})( jQuery );

