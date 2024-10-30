jQuery(function($) {
	$(document).on('vcPanel.shown', function() {
		if (jQuery.fn.select2) {
			$("select.wpb_vc_param_value.dropdown, .dropdown_multi_posts, .dropdown_multi_terms").select2();
		}

		var gridTab = $('div[data-vc-shortcode="wpgb_grid_jvh"] #vc_edit-form-tab-0');

		$('select.id', gridTab).change(function() {
			setEditGridLink();
		});

		if ( hasGridWindow() ) {
			setEditGridLink();
		}

		function hasSelectedGrid() {
			return getSelectedGridId() != '';
		}

		function getSelectedGridId() {
			return $('select.id', gridTab).val();
		}

		function hasGridWindow() {
			return gridTab.length > 0;
		}

		function setEditGridLink() {
			deleteEditLink();

			if ( ! hasSelectedGrid() ) {
				return;
			}

			gridTab.append('<div class="vc_col-xs-12 vc_column" id="edit_grid_link_wrapper"><a class="vc_btn vc_btn-primary" href="' + editGrid.edit_href_base + getSelectedGridId() + '" target="blank">Edit selected Grid in new tab</a></div>');
		}

		function deleteEditLink() {
			$('#edit_grid_link_wrapper').remove();
		}
	});
});
