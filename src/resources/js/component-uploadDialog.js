/**
 * CDT.cmp.uploadDialog(config)
 *
 * @param object config Configuration to pass to the uploader.  Must define
 *   at least an action.
 */
(function ($, CDT, undefined) {

  if (CDT.cmp === undefined) {
    CDT.cmp = {};
  }

  CDT.cmp.uploadDialog = function (config) {
    var elm, uploader;

    elm = $('<div class="file-uploader-dialog"/>')
      .attr('title', 'Upload New Photos')
      .dialog({
        autoOpen: false,
        width: $(window).width() * 0.5,
        height: $(window).height() * 0.66,
        modal: true,
        resizable: false
      });

    // Make the file upload button behave like a jQuery UI button
    elm.bind('dialogopen', function () {
      $(this).find('.qq-upload-button')
        .width('auto')
        .addClass('ui-button')
        .addClass('ui-widget')
        .addClass('ui-state-default')
        .addClass('ui-corner-all')
        .addClass('ui-button-text-icon-primary')
        .wrapInner('<span class="ui-button-text"/>')
        .prepend($('<span/>')
          .addClass('ui-button-icon-primary ui-icon ui-icon-folder-open')
        )
        .mouseenter(function () {
          $(this).addClass('ui-state-hover');
        })
        .mouseleave(function () {
          $(this).removeClass('ui-state-hover');
        });
    });

    uploader = new qq.FileUploader($.extend(config, {
      element: elm[0]
    }));

    return elm;
  };

} (jQuery, CDT));
