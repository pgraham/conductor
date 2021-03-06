"use strict";
(function (exports, $, CDT, undefined) {

	$.widget('ui.editable', {
		options: {
			showOnHover: true
		},

		_create: function () {
			var self = this, txt;

			this.editorOpen = false;
			this.editor = $('<input/>')
				.addClass('inplace-editor')
				.hide()
				.appendTo('body')
				.css('position', 'absolute')
				.attr('title', _L('tt.edit.toSave'))
				.tooltip()
				.blur(function () {
					self._hideEditor();
				})
				.keyup(function (e) {
					if (e.which === KeyEvent.DOM_VK_RETURN) {
						self._submit();
					} else if (e.which === KeyEvent.DOM_VK_ESCAPE) {
						self._hideEditor();
					}
				});
			if (this.options.placeholder) {
				this.editor.attr('placeholder', this.options.placeholder);
			}

			this.textEl = $('<span class="editable-text"/>');
			this._setText(this.element.text());

			this.editBtn = $('<button/>')
				.button({
					label: _L('lbl.edit').ucfirst(),
					text: false,
					icons: { primary: 'ui-icon-pencil' }
				})
				.click(function () {
					self._showEditor();
				});

			if (this.options.showOnHover) {
				this.editBtn.fadeTo(0, 0.1);
				this.element.hover(
					function () {
						self.editBtn.stop(true, true).fadeTo('slow', 1);
					},
					function () {
						self.editBtn.stop(true, true).fadeTo('fast', 0.3);
					}
				);
			}

			if (this.options.editButtonTooltip) {
				this.editBtn.attr('title', this.options.editButtonTooltip).tooltip();
			}

			this.element
				.empty()
				.append(this.textEl)
				.append(this.editBtn)
				.addClass('editable');
		},
		_setOptions: function (key, value) {
		},
		_destroy: function () {
			this.editor.remove();
			this.editBtn.button('destroy').remove();

			this.element.text(this.textEl.text());
			this.textEl.remove();
		},

		_hideEditor: function () {
			var self = this;

			if (!this.editorOpen) {
				return;
			}
			this.editorOpen = false;

			// Queue effects on an empty jQuery object so that they happen
			// sequentially
			$({})
				.queue(function (next) {
					self.editor.blur();
					self.editor.fadeOut('fast', next);
				})
				.queue(function (next) {
					self.textEl.fadeTo('fast', 1);
					self.editBtn.fadeIn('fast', next);
				});
		},

		_setText: function (txt) {
			if (txt) {
				this.textEl.text(txt);
			} else if (this.options.placeholder) {
				this.textEl.html(
					'<span class="placeholder">' + this.options.placeholder + '</span>'
				);
			}
		},

		_showEditor: function () {
			var self = this, txt;

			if (this.editorOpen) {
				return;
			}
			this.editorOpen = true;

			// Briefly show the editor to size/position it then hide it again so
			// that its display can be animated
			txt = this.textEl.contents().filter(function () {
				return this.nodeType === 3;
			}).text()
			this.editor
				.val(txt)
				.show()
				.position({
					my: 'left top',
					at: 'left top',
					of: this.textEl
				})
				.css('right', this.editor.css('left'))
				.hide();

			$({})
				.queue(function (next) {
					self.textEl.fadeTo('fast', 0.05);
					self.editBtn.fadeOut('fast', next);
				})
				.queue(function (next) {
					self.editor.fadeIn('fast', next);
				})
				.queue(function (next) {
					self.editor.focus();
					next();
				});
		},

		_submit: function () {
			var self = this, newVal = this.editor.val();

			this.options.save(newVal, function () {
				self._setText(newVal);
				self._hideEditor();
			});
		}
	});

	exports.editable = function (opts) {
		return $('<div/>').editable(opts || {});
	};

} (CDT.ns('CDT.widget'), jQuery, CDT));
