<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_dropdown_multi_posts_output( $param, $values ) {
   $output = '<select multiple name="'. esc_attr( $param['param_name'] ).'" class="wpb_vc_param_value wpb-input wpb-select '. esc_attr( $param['param_name'] ).' '. esc_attr($param['type']).'">';

   if ( ! is_array( $values ) ) {
	   $values = explode( ',', $values );
   }

   $values = array_filter( $values );

   foreach ( \JVH\Gridbuilder\GridVcElement::getAllPosts() as $post ) {
	   $selected = '';

	   if ( in_array( $post->ID, $values ) ) {
		   $selected = 'selected="selected"';
	   }

	   $output .= "<option value=\"$post->ID\" $selected>$post->post_title ($post->post_type #$post->ID)</option>";
   }

   $output .= '</select>';

   return $output;
}
if ( function_exists( 'vc_add_shortcode_param' ) ) {
	vc_add_shortcode_param( 'dropdown_multi_posts', 'get_dropdown_multi_posts_output' );
}

function get_dropdown_multi_terms_output( $param, $values ) {
   $output = '<select multiple name="'. esc_attr( $param['param_name'] ).'" class="wpb_vc_param_value wpb-input wpb-select '. esc_attr( $param['param_name'] ).' '. esc_attr($param['type']).'">';

   if ( ! is_array( $values ) ) {
	   $values = explode( ',', $values );
   }

   $values = array_filter( $values );

   foreach ( \JVH\Gridbuilder\GridVcElement::getAllTerms() as $term ) {
	   $selected = '';

	   if ( in_array( $term->term_id, $values ) ) {
		   $selected = 'selected="selected"';
	   }

	   $output .= "<option value=\"$term->term_id\" $selected>$term->name ($term->taxonomy #$term->term_id)</option>";
   }

   $output .= '</select>';

   return $output;
}
if ( function_exists( 'vc_add_shortcode_param' ) ) {
	vc_add_shortcode_param( 'dropdown_multi_terms', 'get_dropdown_multi_terms_output' );
}
