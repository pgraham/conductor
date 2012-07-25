/**
 * This script defines the CDT.layout.hblock function which return an augmented
 * jQuery object containing a single <div/> element which will layout its
 * children in horizontal blocks.
 *
 * Usage:
 * ------
 * var container = CDT.layout.hblock()
 *   .add( ... )
 *   .add( ... )
 *   .appendTo( jQuery )
 *
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
if (window['CDT'] === undefined) {
  var CDT = {};
}
if (CDT.layout === undefined) {
  CDT.layout = {};
}
(function ( $, CDT, undefined ) {

  CDT.layout.hblock = function () {
    var elm;

    elm = $('<div class="hblock-container"/>');
    elm.add = function (jq, lbl) {
      var block = $('<div class="hblock"/>').appendTo(this);
      if (lbl) {
        $('<span class="hblock-lbl"/>').html(lbl).appendTo(block);
      }
      block.append(jq);
      return this;
    }
    return elm;
  };

} ( jQuery, CDT ));
