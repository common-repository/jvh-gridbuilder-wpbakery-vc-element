jQuery(function($) {
	$(document).on('vcPanel.shown', function() {
		// Trigger change so negative values change from undefined to correct value on load VC popup panel
		setTimeout(function () {
			$('.ultimate-responsive-wrapper').each(function() {
				setHiddenField($(this));
			});
		}, 500);

		$('.ult-responsive-input').change(function() {
			var wrapper = $(this).closest('.ultimate-responsive-wrapper');

			setHiddenField(wrapper);
		});

		function setHiddenField(wrapper) {
			var hiddenField = $('.ultimate_responsive_field', wrapper);
			var values = '';

			$('.ult-responsive-input', wrapper).each(function() {
				if ($(this).val() === '') {
					return;
				}

				var device = $(this).attr('data-id');
				var unit = $(this).attr('data-unit');
				values += device + ':' + $(this).val() + unit + ';';
			});

			$('.ultimate_responsive_field', wrapper).val(values);
		}
	});
});
