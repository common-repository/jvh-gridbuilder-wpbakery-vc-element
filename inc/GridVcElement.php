<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GridVcElement
{
	public function activate()
	{
		$this->addVcElement();
		$this->addEditButtonJs();
		$this->addSelect2Css();
	}

	private function addVcElement() : void
	{
		add_action( 'vc_before_init', function() {
			$args = [
				'name'             => 'Gridbuilder Grid',
				'base'             => 'wpgb_grid_jvh',
				'icon'             => get_gridbuilder_icon_url(),
				'weight'           => 2,
				'category' => 'JVH',
				'params'           => [
					[
						'type'        => 'dropdown',
						'heading'     => 'Selected grid',
						'param_name'  => 'id',
						'value'       => self::getGridChoises(),
						'admin_label' => true,
					],
					[
						'type'        => 'checkbox',
						'heading'     => 'Use different grid for mobile',
						'param_name'  => 'different_mobile_grid',
						'admin_label' => true,
					],
					[
						'type'        => 'dropdown',
						'heading'     => 'Selected mobile grid',
						'param_name'  => 'id_mobile',
						'value'       => self::getGridChoises(),
						'dependency' => [
							'element' => 'different_mobile_grid',
							'value' => 'true',
						],
						'admin_label' => true,
					],
					[
						'type'        => 'dropdown',
						'heading'     => 'Grid type',
						'param_name'  => 'grid_type',
						'value'       => [
							'Select grid type' => '',
							'Regular' => 'regular',
							'Main query' => 'main_query',
							'Custom query' => 'custom_query',
							'Related' => 'related',
							'Cross-sell' => 'cross_sell',
							'Upsell' => 'upsell',
							'Video gallery' => 'video_gallery',
							'Image gallery' => 'image_gallery',
						],
						'admin_label' => true,
					],
					[
						'type'        => 'checkbox',
						'heading'     => 'Show upsell products instead of related products if there are any',
						'param_name'  => 'add_upsell_related',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'related',
						],
					],
					[
					  'type'        => 'textfield',
					  'heading'     => 'CSS class',
					  'param_name'  => 'css_class',
					  'group'      => 'Design options',
					],
					[
						'type'       => 'css_editor',
						'heading'    => 'CSS',
						'param_name' => 'css',
						'group'      => 'Design options',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Padding left & right',
						'param_name' => 'padding_lr',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Margin left & right',
						'param_name' => 'margin_lr',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Margin top',
						'param_name' => 'margin_top',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Margin bottom',
						'param_name' => 'margin_bottom',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Padding top',
						'param_name' => 'padding_top',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Padding bottom',
						'param_name' => 'padding_bottom',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type'       => 'ultimate_responsive',
						'heading'    => 'Arrows margin top',
						'description' => 'You can use a negative margin to align carousel/slider arrows with title heading above.',
						'param_name' => 'arrows_margin_top',
						'unit'       => 'px',
						'media'      => array(
							'Desktop'          => '',
							'Tablet'           => '',
							'Tablet Portrait'  => '',
							'Mobile Landscape' => '',
							'Mobile'           => '',
						),
						'admin_label' => true,
						'group' => 'Padding & Margins',
					],
					[
						'type' => 'posttypes',
						'heading' => 'Post types',
						'param_name' => 'post_types',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'custom_query',
						],
					],
					[
						'type' => 'dropdown_multi_posts',
						'heading' => 'Include posts',
						'param_name' => 'post__in',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'custom_query',
						],
					],
					[
						'type' => 'dropdown_multi_posts',
						'heading' => 'Exclude posts',
						'param_name' => 'post__not_in',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'custom_query',
						],
					],
					[
						'type' => 'dropdown_multi_terms',
						'heading' => 'Include categories',
						'param_name' => 'terms',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'custom_query',
						],
					],
					[
						'type' => 'dropdown_multi_terms',
						'heading' => 'Exclude categories',
						'param_name' => 'terms__not_in',
						'admin_label' => true,
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'custom_query',
						],
					],
					[
					  'type'        => 'textarea_raw_html',
					  'heading'     => 'Video urls',
					  'param_name'  => 'video_urls',
					  'description' => 'One (Youtube) video url per line.',
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'video_gallery',
						],
					],
					[
					  'type'        => 'attach_images',
					  'heading'     => 'Images',
					  'param_name'  => 'images',
						'dependency' => [
							'element' => 'grid_type',
							'value' => 'image_gallery',
						],
					],
				],
			];

			if ( ! class_exists( 'Ultimate_VC_Addons' ) ) {
				foreach ( $args['params'] as $key => $param ) {
					if ( $param['type'] === 'ultimate_responsive' ) {
						unset( $args['params'][$key] );
					}
				}

				$args['params'][] = [
					'type'       => 'notice',
					'heading'    => 'You need no activate Ultimate VC Addons to be able to use responsive margins.',
					'param_name' => 'ultimate_required',
					'group' => 'Padding & Margins',
				];
			}

			vc_map( $args );
		} );
	}

	private function addEditButtonJs() : void
	{
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_script( 'edit-grid-button', plugin_dir_url( __DIR__ ) . '/assets/js/edit-grid-button.js', [], '1.1' );

			wp_localize_script( 'edit-grid-button', 'editGrid', [
				'edit_href_base' => $this->get_edit_href_base(),
			] );

			wp_enqueue_script( 'negative-responsive-values', plugin_dir_url( __DIR__ ) . '/assets/js/negative-responsive-values.js', [], '1.0' );
		} );
	}

	private function get_edit_href_base() : string
	{
		$edit_href_base = admin_url( 'admin.php?page=wp-grid-builder&menu=grids&id=' );

		if ( is_legacy_gridbuilder() ) {
			$edit_href_base = admin_url( 'admin.php?page=wpgb-grid-settings&id=' );
		}

		return $edit_href_base;
	}

	private function addSelect2Css() : void
	{
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_style( 'vc-select2', plugin_dir_url( __DIR__ ) . '/assets/css/vc-select2.css' );
		} );
	}

	private function getRelevantTerms() : array
	{
		$relevant_terms = [];

		foreach ( $this->getAllTerms() as $term ) {
			if ( in_array( $term->taxonomy, $this->getIrrelevantTaxonomyNames() ) ) {
				continue;
			}

			$relevant_terms[] = $term;
		}

		return $relevant_terms;
	}

	public static function getAllTerms() : array
	{
		$terms = get_transient( 'jvh_all_terms' );

		if ( $terms === false ) {
			$terms = get_terms();

			set_transient( 'jvh_all_terms', $terms, MINUTE_IN_SECONDS * 10 );
		}

		return $terms;
	}

	private function getIrrelevantTaxonomyNames() : array
	{
		return [
			'yst_prominent_words',
			'nav_menu',
			'vc_snippet_cat',
		];
	}

	public static function getAllPosts() : array
	{
		$posts = get_transient( 'jvh_all_posts' );

		$posts = false;

		if ( $posts === false ) {
			$the_query = new \WP_Query([
				'posts_per_page' => -1,
				'post_type' => 'any',
				'orderby' => 'title',
				'order' => 'ASC',
			]);

			$posts = $the_query->posts;

			set_transient( 'jvh_all_posts', $posts, MINUTE_IN_SECONDS * 3 );
		}

		return $posts;
	}

	public static function getGridChoises() : array
	{
		global $wpdb;

		$grids = [];

		$grids['Select grid'] = '';

		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpgb_grids", OBJECT );

		foreach ( $results as $grid ) {
			$grids["$grid->id: $grid->name"] = $grid->id;
		}

		return $grids;
	}
}
