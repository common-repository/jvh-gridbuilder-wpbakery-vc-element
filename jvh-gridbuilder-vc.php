<?php
/**
 * Plugin Name:       JVH Gridbuilder WPBakery VC Element
 * Description:       WPBakery Gridbuilder VC Element
 * Version:           2.8.0
 * Author:            JVH webbouw
 * Author URI:        https://jvhwebbouw.nl
 * License:           GPL-v3
 * Requires PHP:      7.3
 * Requires at least: 5.0
 */

foreach ( glob( __DIR__ . '/inc/*.php' ) as $file ) {
    require_once $file;
}

$grid_element = new \JVH\Gridbuilder\GridElement();
$grid_element->activate();

$facet_element = new \JVH\Gridbuilder\FacetElement();
$facet_element->activate();

if ( ! is_admin() ) {
	$query_handler = \JVH\Gridbuilder\QueryHandler::handle();
}

function empty_jvh_terms_transient() {
    delete_transient( 'jvh_all_terms' );
}
add_action( 'create_term', 'empty_jvh_terms_transient' );

function get_gridbuilder_version() {
    $plugin_path = WP_PLUGIN_DIR . '/wp-grid-builder/wp-grid-builder.php';

    if (file_exists($plugin_path)) {
        $plugin_data = get_plugin_data($plugin_path);

        return $plugin_data['Version'];
    } else {
        return false;
    }
}

function is_legacy_gridbuilder() {
	$version = get_gridbuilder_version();

	return version_compare($version, '2.0', '<');
}
function get_gridbuilder_icon_url() {
	$icon_url = content_url() . '/plugins/jvh-gridbuilder-wpbakery-vc-element/assets/img/gridbuilder-icon.svg';

	if ( is_legacy_gridbuilder() ) {
		$icon_url = content_url() . '/plugins/wp-grid-builder/admin/assets/svg/icon.svg';
	}

	return $icon_url;
}
