/**
 * Tabbed Dialog.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
CDT.tabbedDialog = function (spec) {
  var that,
      // Private members
      dialog, tabs, tabHeaders,

      // Private functions
      show, close,

      // Interation variables
      prop;

  dialog = $('<div/>')
    .attr('title', spec.title)
    .append( $('<div/>').append( $('<ul/>') ));

  tabs = dialog.find('div');
  tabHeaders= tabs.find('ul');

  for (prop in spec.tabs) {
    if (spec.tabs.hasOwnProperty(prop)) {
      tabHeaders.append(
        $('<li/>').append(
          $('<a/>')
            .attr('href', '#tabs-' + prop)
            .text(prop)
        )
      );

      tabs.append(
        $('<div/>')
          .attr('id', 'tabs-' + prop)
          .append(spec.tabs[prop]));
    }
  }
  tabs.tabs();

  that = observable({});

  close = function () {
    dialog.dialog('close');
    that.trigger('close');
  }
  that.close = close;

  show = function () {
    dialog.dialog({
      modal: true,
      buttons: spec.btns,
      dialogClass: 'cdt-TabbedDialog',
      width:605
    });
  };
  that.show = show;

  return that;
}
