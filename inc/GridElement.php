<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GridElement
{
	public function activate() : void
	{
		$this->addShortcode();

		if ( is_admin() ) {
			$this->activateVcElement();
		}
	}

	private function addShortcode() : void
	{
		add_shortcode( 'wpgb_grid_jvh', function( $atts ) {
			if ( ! is_array( $atts ) ) {
				return;
			}

			$grid_id = $this->getGridId( $atts );
			$page_identifier = $this->getPageIdentifier();
			$atts['id'] = $grid_id;

			/*
			 * Saving shortcode atts so it can be used later to filter query
			 * No other way to access shortcode atts
			 */
			set_transient( "jvh_grid_atts_{$grid_id}_{$page_identifier}", $atts, DAY_IN_SECONDS );
			
			// Save last grid id for wp_grid_builder/grid/the_objects filter for video posts
			set_transient( "jvh_grid_id_{$page_identifier}", $atts['id'], DAY_IN_SECONDS );

			$shortcode = new \JVH\Gridbuilder\GridShortcode( $atts );

			return $shortcode->getOutput();
		} );
	}

	private function getGridId( $atts )
	{
		if ( $this->hasDifferentMobileGrid( $atts ) && $this->isMobile() ) {
			return $atts['id_mobile'];

		}
		else {
			return $atts['id'];
		}
	}

	private function getPageIdentifier()
	{
		if ( is_singular() ) {
			return get_the_ID();
		}
		else {
			$url_path = strtok( $_SERVER['REQUEST_URI'], '?' );

			return sanitize_title( $url_path );
		}
	}

	private function hasDifferentMobileGrid( $atts )
	{
		return $atts['different_mobile_grid'] == 'true';
	}

	private function isMobile()
	{
		require_once __DIR__ . '/../lib/Mobile_Detect.php';
		$detect = new \Mobile_Detect();

		return $detect->isMobile();
	}

	private function activateVcElement() : void
	{
		$vc_element = new \JVH\Gridbuilder\GridVcElement();
		$vc_element->activate();
	}
}
