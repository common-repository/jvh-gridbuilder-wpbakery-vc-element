jQuery(function($) {
	$(document).on('vcPanel.shown', function() {
		var facetTab = $('div[data-vc-shortcode="wpgb_facet_jvh"] #vc_edit-form-tab-0');

		$('select.id', facetTab).change(function() {
			setEditFacetLink();
		});

		if ( hasFacetWindow() ) {
			setEditFacetLink();
		}

		function hasSelectedFacet() {
			return getSelectedFacetId() != '';
		}

		function getSelectedFacetId() {
			return $('select.id', facetTab).val();
		}

		function hasFacetWindow() {
			return facetTab.length > 0;
		}

		function setEditFacetLink() {
			deleteEditFacetLink();

			if ( ! hasSelectedFacet() ) {
				return;
			}

			facetTab.append('<div class="vc_col-xs-12 vc_column" id="edit_facet_link_wrapper"><a class="vc_btn vc_btn-primary" href="' + editFacet.edit_href_base + getSelectedFacetId() + '" target="blank">Edit selected Facet in new tab</a></div>');
		}

		function deleteEditFacetLink() {
			$('#edit_facet_link_wrapper').remove();
		}
	});
});
