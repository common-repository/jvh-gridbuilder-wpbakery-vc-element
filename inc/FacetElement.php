<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FacetElement
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
		add_shortcode( 'wpgb_facet_jvh', function( $atts ) {
			$shortcode = new \JVH\Gridbuilder\FacetShortcode( $atts );

			return $shortcode->getOutput();
		} );
	}

	private function activateVcElement() : void
	{
		$vc_element = new \JVH\Gridbuilder\FacetVcElement();
		$vc_element->activate();
	}
}
