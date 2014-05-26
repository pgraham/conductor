(function ($) {

	function getIsoDate(d) {
		return d.toString('yyyy-MM-dd');
	}

	function toggleHoverState() {
		$(this).toggleClass('ui-state-hover');
	}

	$.widget( 'ui.daterangepicker', {

		options: {
			readOnly: true,
			value: 'yesterday - 1 month TO yesterday',
			dateFormat: 'MMMM dS, yyyy'
		},

		_buildDateDisplay: function () {
			return $('<span class="ui-daterangepicker-date"/>');
		},

		_create: function () {
			var self = this;

			this.from = this._buildDateDisplay().addClass('ui-daterangepicker-from');
			this.to = this._buildDateDisplay().addClass('ui-daterangepicker-to');

			this.pickerFrom = $('<div/>').addClass('ui-datepicker-from').datepicker();
			this.pickerTo = $('<div/>').addClass('ui-datepicker-to').datepicker();
			this.picker = $('<div/>')
				.addClass('ui-daterangepicker-picker')
				.addClass('ui-widget ui-widget-content ui-corner-all')
				.css('position', 'absolute')
				.append( $('<div class="ui-daterangepicker-datepicker"/>')
					.append( $('<div>From:</div>') )
					.append( $(this.pickerFrom) )
				)
				.append( $('<div class="ui-daterangepicker-datepicker"/>')
					.append( $('<div>To:</div>') )
					.append(this.pickerTo)
				);

			this.element.addClass('hasDateRangePicker').wrap('<div/>').hide();
			this.element.parent()
				.addClass('ui-daterangepicker ui-widget ui-state-default ui-corner-all')
				.append(this.from)
				.append( $('<span/>')
					.addClass('ui-daterangepicker-separator')
					.text(' - ')
				)
				.append(this.to)
				.append( $('<span class="ui-icon ui-icon-triangle-1-s"/>') )
				.hover(toggleHoverState, toggleHoverState)
				.toggle(
					function () {
						self._showPicker();
					},
					function () {
						self._hidePicker();
					}
				);

			this._setValue(this.options.value);
		},

		_destroy: function () {
		},

		getFrom: function () {
			return this.from.data('date');
		},

		getRange: function () {
			return getIsoDate(this.getFrom()) + ' TO ' + getIsoDate(this.getTo());
		},

		getTo: function () {
			return this.to.data('date');
		},

		_hidePicker: function () {
			this.from.data('date', this.pickerFrom.datepicker('getDate'));
			this.to.data('date', this.pickerTo.datepicker('getDate'));

			this._setValue(this.getRange());

			this.picker.detach();

			this._trigger('select');
		},

		_setOption: function (key, value) {
			switch (key) {
				case 'value':
				this._setValue(value);
				break;
			}

			this._super('_setOption', key, value);
		},
		
		_setValue: function (value) {
			var range = value.split(/\s+TO\s+/i),
					fromDate = Date.parse(range[0]),
					toDate = Date.parse(range[1]);

			this.from.data('date', fromDate);
			this.to.data('date', toDate);

			this.from.text(fromDate.toString(this.options.dateFormat));
			this.to.text(toDate.toString(this.options.dateFormat)); 

			this.element.val(this.getRange());
		},

		_showPicker: function () {
			this.pickerFrom.datepicker('setDate', this.getFrom());
			this.pickerTo.datepicker('setDate', this.getTo());

			this.picker.appendTo('body').position({
				my: 'left top',
				at: 'left bottom',
				of: this.element.parent()
			});
		}
	});
} (jQuery))
