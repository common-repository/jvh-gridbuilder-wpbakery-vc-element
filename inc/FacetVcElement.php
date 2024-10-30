<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FacetVcElement
{
	public function activate()
	{
		$this->addVcElement();
		$this->addEditButtonJs();
	}

	public function addVcElement()
	{
		add_action( 'vc_before_init', function() {
			vc_map( [
				'name'             => 'Gridbuilder Facet',
				'base'             => 'wpgb_facet_jvh',
				'icon'             => get_gridbuilder_icon_url(),
				'weight'           => 1,
				'category' => 'JVH',
				'params'           => [
					[
						'type'        => 'dropdown',
						'heading'     => 'Selected facet',
						'param_name'  => 'id',
						'value'       => $this->getFacetChoises(),
						'admin_label' => true,
					],
					[
						'type'        => 'dropdown',
						'heading'     => 'Selected grid',
						'param_name'  => 'grid',
						'value'       => \JVH\Gridbuilder\GridVcElement::getGridChoises(),
						'admin_label' => true,
					],
					[
						'type'       => 'css_editor',
						'heading'    => 'CSS',
						'param_name' => 'css',
						'group'      => 'Design options',
					],
				],
			] );
		} );
	}

	public function addEditButtonJs() : void
	{
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_script( 'edit-facet-button', plugin_dir_url( __DIR__ ) . '/assets/js/edit-facet-button.js', [], '1.1' );

			wp_localize_script( 'edit-facet-button', 'editFacet', [
				'edit_href_base' => $this->get_edit_href_base(),
			] );
		} );
	}

	private function get_edit_href_base() {
		$edit_href_base = admin_url( 'admin.php?page=wp-grid-builder&menu=facets&id=' );

		if ( is_legacy_gridbuilder() ) {
			$edit_href_base = admin_url( 'admin.php?page=wpgb-facet-settings&id=' );
		}

		return $edit_href_base;
	}

	private function getFacetChoises() : array
	{
		global $wpdb;

		$grids = [];

		$grids['Select facet'] = '';

		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpgb_facets", OBJECT );

		foreach ( $results as $grid ) {
			$grids["$grid->id: $grid->name"] = $grid->id;
		}

		return $grids;
	}
}
